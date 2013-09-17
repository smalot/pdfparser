<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-2.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * The content of this class has been adapted from a file of the TCPDF project.
 * Read the following file header.
 */

namespace Smalot\PdfParser;

//============================================================+
// File name   : tcpdf_filters.php
// Version     : 1.0.000
// Begin       : 2011-05-23
// Last Update : 2012-01-28
// Author      : Nicola Asuni - Tecnick.com LTD - Manor Coach House, Church Hill, Aldershot, Hants, GU12 4RQ, UK - www.tecnick.com - info@tecnick.com
// License     : http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT GNU-LGPLv3
// -------------------------------------------------------------------
// Copyright (C) 2011-2012  Nicola Asuni - Tecnick.com LTD
//
// This file is part of TCPDF software library.
//
// TCPDF is free software: you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// TCPDF is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the License
// along with TCPDF. If not, see
// <http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT>.
//
// See LICENSE.TXT file for more information.
// -------------------------------------------------------------------
//
// Description : This is a PHP class for decoding common PDF filters (PDF -2008 - 7.4 Filters).
//
//============================================================+
use Smalot\PdfParser\Filter\ASCII85;
use Smalot\PdfParser\Filter\ASCIIHex;
use Smalot\PdfParser\Filter\Flate;
use Smalot\PdfParser\Filter\LZW;
use Smalot\PdfParser\Filter\RunLength;
use Smalot\PdfParser\Header;

/**
 * Class Filters
 *
 * @package Smalot\PdfParser
 */
class Filters
{
    /**
     * @param string $filter
     * @param string $data
     * @param array  $params
     *
     * @return string
     * @throw \Exception
     */
    public static function decodeFilter($filter, $data, $params = array())
    {
        switch ($filter) {
            case 'ASCIIHexDecode':
                return ASCIIHex::decode($data);

            case 'ASCII85Decode':
                return ASCII85::decode($data);

            case 'LZWDecode':
                return LZW::decode($data, $params);

            case 'FlateDecode':
                return Flate::decode($data, $params);

            case 'RunLengthDecode':
                return RunLength::decode($data);

//            case 'CCITTFaxDecode':
//            case 'JBIG2Decode':
//            case 'DCTDecode':
//            case 'JPXDecode':
//            case 'Crypt':

            default:
                trigger_error('Unsupported filter.');

                return $data;
        }
    }

    /**
     * Paeth prediction function
     *
     * @param integer $a
     * @param integer $b
     * @param integer $c
     *
     * @return integer
     */
    protected static function paeth($a, $b, $c)
    {
        // $a - left, $b - above, $c - upper left
        $p  = $a + $b - $c; // initial estimate
        $pa = abs($p - $a); // distances to a, b, c
        $pb = abs($p - $b);
        $pc = abs($p - $c);

        // return nearest of a,b,c,
        // breaking ties in order a,b,c.
        if ($pa <= $pb && $pa <= $pc) {
            return $a;
        } else {
            if ($pb <= $pc) {
                return $b;
            } else {
                return $c;
            }
        }
    }

    /**
     * Convert stream data according to the filter params set after decoding.
     *
     * @param string $data
     * @param Header $params
     *
     * @return string
     * @throws \Exception
     */
    public static function applyDecodeParams($data, Header $params)
    {

        $predictor        = $params->get('Predictor')->getContent();
        $colors           = $params->get('Colors')->getContent();
        $bitsPerComponent = $params->get('BitsPerComponent')->getContent();
        $columns          = $params->get('Columns')->getContent();

//        var_dump($predictor, $colors, $bitsPerComponent, $columns);

        // Set default values.
        if (!$predictor) {
            $predictor = 1;
        }
        if (!$colors) {
            $colors = 1;
        }
        if (!$bitsPerComponent) {
            $bitsPerComponent = 8;
        }
        if (!$columns) {
            $columns = 1;
        }

        /** None of prediction */
        if ($predictor == 1) {
            return $data;
        }

        /** TIFF Predictor 2 */
        if ($predictor == 2) {
            throw new \Exception('Not implemented yet');
        }

        /**
         * PNG prediction
         * Prediction code is duplicated on each row.
         * Thus all cases can be brought to one
         */
        if ($predictor == 10 || /** None of prediction */
            $predictor == 11 || /** Sub prediction     */
            $predictor == 12 || /** Up prediction      */
            $predictor == 13 || /** Average prediction */
            $predictor == 14 || /** Paeth prediction   */
            $predictor == 15
            /** Optimal prediction */
        ) {

            $bitsPerSample  = $bitsPerComponent * $colors;
            $bytesPerSample = ceil($bitsPerSample / 8);
            $bytesPerRow    = ceil($bitsPerSample * $columns / 8);
            $rows           = ceil(strlen($data) / ($bytesPerRow + 1));
            $output         = '';
            $offset         = 0;

            $lastRow = array_fill(0, $bytesPerRow, 0);
            for ($count = 0; $count < $rows; $count++) {
                $lastSample = array_fill(0, $bytesPerSample, 0);
                switch (ord($data[$offset++])) {
                    case 0: // None of prediction
                        $output .= substr($data, $offset, $bytesPerRow);
                        for ($count2 = 0; $count2 < $bytesPerRow && $offset < strlen($data); $count2++) {
                            $lastSample[$count2 % $bytesPerSample] = $lastRow[$count2] = ord($data[$offset++]);
                        }
                        break;

                    case 1: // Sub prediction
                        for ($count2 = 0; $count2 < $bytesPerRow && $offset < strlen($data); $count2++) {
                            $decodedByte                           = (ord(
                                        $data[$offset++]
                                    ) + $lastSample[$count2 % $bytesPerSample]) & 0xFF;
                            $lastSample[$count2 % $bytesPerSample] = $lastRow[$count2] = $decodedByte;
                            $output .= chr($decodedByte);
                        }
                        break;

                    case 2: // Up prediction
                        for ($count2 = 0; $count2 < $bytesPerRow && $offset < strlen($data); $count2++) {
                            $decodedByte                           = (ord($data[$offset++]) + $lastRow[$count2]) & 0xFF;
                            $lastSample[$count2 % $bytesPerSample] = $lastRow[$count2] = $decodedByte;
                            $output .= chr($decodedByte);
                        }
                        break;

                    case 3: // Average prediction
                        for ($count2 = 0; $count2 < $bytesPerRow && $offset < strlen($data); $count2++) {
                            $decodedByte                           = (ord($data[$offset++]) +
                                    floor(($lastSample[$count2 % $bytesPerSample] + $lastRow[$count2]) / 2)
                                ) & 0xFF;
                            $lastSample[$count2 % $bytesPerSample] = $lastRow[$count2] = $decodedByte;
                            $output .= chr($decodedByte);
                        }
                        break;

                    case 4: // Paeth prediction
                        $currentRow = array();
                        for ($count2 = 0; $count2 < $bytesPerRow && $offset < strlen($data); $count2++) {
                            $decodedByte                           = (ord($data[$offset++]) +
                                    self::paeth(
                                        $lastSample[$count2 % $bytesPerSample],
                                        $lastRow[$count2],
                                        ($count2 - $bytesPerSample < 0) ?
                                            0 : $lastRow[$count2 - $bytesPerSample]
                                    )
                                ) & 0xFF;
                            $lastSample[$count2 % $bytesPerSample] = $currentRow[$count2] = $decodedByte;
                            $output .= chr($decodedByte);
                        }
                        $lastRow = $currentRow;
                        break;

                    default:
                        throw new \Exception('Unknown prediction tag.');
                }
            }

            return $output;
        }

        throw new \Exception('Unknown prediction algorithm - ' . $predictor . '.');
    }
}

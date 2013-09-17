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

namespace Smalot\PdfParser\Filter;

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

/**
 * Class LZW
 *
 * @package Smalot\PdfParser\Filter
 */
class LZW
{
    /**
     * Decode
     * Decompresses data encoded using the LZW (Lempel-Ziv-Welch) adaptive compression method, reproducing the original text or binary data.
     *
     * @param       $data (string) Data to decode.
     * @param array $params
     *
     * @return string Decoded data string.
     * @public
     * @since 1.0.000 (2011-05-23)
     */
    public static function decode($data, $params)
    {
        // intialize string to return
        $decoded = '';
        // data length
        $data_length = strlen($data);
        // convert string to binary string
        $bitstring = '';
        for ($i = 0; $i < $data_length; ++$i) {
            $bitstring .= sprintf('%08b', ord($data{$i}));
        }
        // get the number of bits
        $data_length = strlen($bitstring);
        // initialize code length in bits
        $bitlen = 9;
        // initialize dictionary index
        $dix = 258;
        // initialize the dictionary (with the first 256 entries).
        $dictionary = array();
        for ($i = 0; $i < 256; ++$i) {
            $dictionary[$i] = chr($i);
        }
        // previous val
        $prev_index = 0;
        // while we encounter EOD marker (257), read code_length bits
        while (($data_length > 0) AND (($index = bindec(substr($bitstring, 0, $bitlen))) != 257)) {
            // remove read bits from string
            $bitstring = substr($bitstring, $bitlen);
            // update number of bits
            $data_length -= $bitlen;
            if ($index == 256) { // clear-table marker
                // reset code length in bits
                $bitlen = 9;
                // reset dictionary index
                $dix        = 258;
                $prev_index = 256;
                // reset the dictionary (with the first 256 entries).
                $dictionary = array();
                for ($i = 0; $i < 256; ++$i) {
                    $dictionary[$i] = chr($i);
                }
            } elseif ($prev_index == 256) {
                // first entry
                $decoded .= $dictionary[$index];
                $prev_index = $index;
            } else {
                // check if index exist in the dictionary
                if ($index < $dix) {
                    // index exist on dictionary
                    $decoded .= $dictionary[$index];
                    $dic_val = $dictionary[$prev_index] . $dictionary[$index]{0};
                    // store current index
                    $prev_index = $index;
                } else {
                    // index do not exist on dictionary
                    $dic_val = $dictionary[$prev_index] . $dictionary[$prev_index]{0};
                    $decoded .= $dic_val;
                }
                // update dictionary
                $dictionary[$dix] = $dic_val;
                ++$dix;
                // change bit length by case
                if ($dix == 2047) {
                    $bitlen = 12;
                } elseif ($dix == 1023) {
                    $bitlen = 11;
                } elseif ($dix == 511) {
                    $bitlen = 10;
                }
            }
        }

        return $decoded;
    }
}
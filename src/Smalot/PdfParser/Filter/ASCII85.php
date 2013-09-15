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
 * Class ASCII85
 *
 * @package Smalot\PdfParser\Filter
 */
class ASCII85
{
    /**
     * Decode
     * Decodes data encoded in an ASCII base-85 representation, reproducing the original binary data.
     *
     * @param $data (string) Data to decode.
     *
     * @return string Decoded data string.
     * @throws \Exception
     * @public
     * @since 1.0.000 (2011-05-23)
     */
    public static function decode($data)
    {
        // initialize string to return
        $decoded = '';
        // all white-space characters shall be ignored
        $data = preg_replace('/[\s]/', '', $data);
        // remove start sequence 2-character sequence <~ (3Ch)(7Eh)
        if (strpos($data, '<~') !== false) {
            // remove EOD and extra data (if any)
            $data = substr($data, 2);
        }
        // check for EOD: 2-character sequence ~> (7Eh)(3Eh)
        $eod = strpos($data, '~>');
        if ($eod !== false) {
            // remove EOD and extra data (if any)
            $data = substr($data, 0, $eod);
        }
        // data length
        $data_length = strlen($data);
        // check for invalid characters
        if (preg_match('/[^\x21-\x75,\x74]/', $data) > 0) {
            throw new \Exception('decodeASCII85: invalid code');
        }
        // z sequence
        $zseq = chr(0) . chr(0) . chr(0) . chr(0);
        // position inside a group of 4 bytes (0-3)
        $group_pos = 0;
        $tuple     = 0;
        $pow85     = array((85 * 85 * 85 * 85), (85 * 85 * 85), (85 * 85), 85, 1);
//        $last_pos  = ($data_length - 1);
        // for each byte
        for ($i = 0; $i < $data_length; ++$i) {
            // get char value
            $char = ord($data[$i]);
            if ($char == 122) { // 'z'
                if ($group_pos == 0) {
                    $decoded .= $zseq;
                } else {
                    throw new \Exception('decodeASCII85: invalid code');
                }
            } else {
                // the value represented by a group of 5 characters should never be greater than 2^32 - 1
                $tuple += (($char - 33) * $pow85[$group_pos]);
                if ($group_pos == 4) {
                    $decoded .= chr($tuple >> 24) . chr($tuple >> 16) . chr($tuple >> 8) . chr($tuple);
                    $tuple     = 0;
                    $group_pos = 0;
                } else {
                    ++$group_pos;
                }
            }
        }
        if ($group_pos > 1) {
            $tuple += $pow85[($group_pos - 1)];
        }
        // last tuple (if any)
        switch ($group_pos) {
            case 4:
            {
                $decoded .= chr($tuple >> 24) . chr($tuple >> 16) . chr($tuple >> 8);
                break;
            }
            case 3:
            {
                $decoded .= chr($tuple >> 24) . chr($tuple >> 16);
                break;
            }
            case 2:
            {
                $decoded .= chr($tuple >> 24);
                break;
            }
            case 1:
            {
                throw new \Exception('decodeASCII85: invalid code');
                break;
            }
        }

        return $decoded;
    }
}
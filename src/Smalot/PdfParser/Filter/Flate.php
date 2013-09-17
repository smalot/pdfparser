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
use Smalot\PdfParser\Filters;

/**
 * Class Flate
 *
 * @package Smalot\PdfParser\Filter
 */
class Flate
{
    /**
     * Decode
     * Decompresses data encoded using the zlib/deflate compression method, reproducing the original text or binary data.
     *
     * @param       $data (string) Data to decode.
     * @param array $params
     *
     * @return string Decoded data string.
     * @throws \Exception
     * @public
     * @since 1.0.000 (2011-05-23)
     */
    public static function decode($data, $params)
    {
        // intialize string to return
        $decoded = @gzuncompress($data);
        if ($decoded === false) {
            throw new \Exception('decodeFlate: invalid code');
        }

        if ($params) {
            $decoded = Filters::applyDecodeParams($data, $params);
        }

        return $decoded;
    }
}
<?php

/**
 * This file is based on code of tecnickcom/TCPDF PDF library.
 *
 * Original author Nicola Asuni (info@tecnick.com) and
 * contributors (https://github.com/tecnickcom/TCPDF/graphs/contributors).
 *
 * @see https://github.com/tecnickcom/TCPDF
 *
 * Original code was licensed on the terms of the LGPL v3.
 *
 * ------------------------------------------------------------------------------
 *
 * @file This file is part of the PdfParser library.
 *
 * @author  Alastair Irvine <alastair@plug.org.au>
 *
 * @date    2024-01-12
 *
 * @license LGPLv3
 *
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2017 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 */

namespace Smalot\PdfParser;

/**
 */
class Utils
{
    /**
     * Convert an integer to a string of @p $numBytes bytes, LSB first.
     *
     * @return byte string representing a little-endian integer
     */
    static function lowestBytesStr($n, $numBytes)
    {
        $result = "";
        for ($i = 0; $i < $numBytes; ++$i) {
            $result .= chr($n & 0xFF);
            $n = $n >> 8;
        }
        return $result;
    }


    /**
     * Create a byte string of a given length.
     *
     * @param $numBytes
     * @param $byte The byte to use for each character, default NUL
     *
     * @return byte string representing a little-endian integer
     */
    static function byteString(int $numBytes, int $byte = 0)
    {
        return \implode(\array_map(
            function($n) { return chr($n); },
            \array_fill(0, $numBytes, $byte)
        ));
    }
}


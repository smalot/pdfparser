<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Dāvis Mosāns <davis.mosans@intelligentsystems.lv>
 * @date    2019-09-17
 *
 * @license LGPLv3
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

namespace Smalot\PdfParser\Encoding;

/**
 * Class PostScriptGlyphs
 */
class PostScriptGlyphs
{
    private static $glyphs = null;

    /**
     * @return array
     *
     * The mapping tables have been converted from https://github.com/OpenPrinting/cups-filters/blob/master/fontembed/aglfn13.c,
     * part of the OpenPrinting/cups-filters package, which itself is licensed under the MIT license and lists this specific code part as:
     * Copyright 2008,2012 Tobias Hoffmann under the Expat license (https://www.gnu.org/licenses/license-list.html#Expat)
     */
    public static function getGlyphs()
    {
        if (null === self::$glyphs) {
            self::$glyphs = json_decode(file_get_contents(__DIR__.'/PostScriptGlyphs.json'), true);
        }

        return self::$glyphs;
    }

    public static function getCodePoint($glyph)
    {
        return hexdec(static::getGlyphs()[$glyph]);
    }
}

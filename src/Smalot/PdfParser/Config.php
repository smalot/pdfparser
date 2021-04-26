<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <hi@inspirito.de>
 * @date    2020-11-22
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

namespace Smalot\PdfParser;

/**
 * This class contains configurations used in various classes. You can override them
 * manually, in case default values aren't working.
 *
 * @see https://github.com/smalot/pdfparser/issues/305
 */
class Config
{
    private $fontSpaceLimit = -50;

    /**
     * Represents: (NUL, HT, LF, FF, CR, SP)
     *
     * @var string
     */
    private $pdfWhitespaces = "\0\t\n\f\r ";

    /**
     * Represents: (NUL, HT, LF, FF, CR, SP)
     *
     * @var string
     */
    private $pdfWhitespacesRegex = '[\0\t\n\f\r ]';

    public function getFontSpaceLimit()
    {
        return $this->fontSpaceLimit;
    }

    public function setFontSpaceLimit($value)
    {
        $this->fontSpaceLimit = $value;
    }

    public function getPdfWhitespaces(): string
    {
        return $this->pdfWhitespaces;
    }

    public function setPdfWhitespaces(string $pdfWhitespaces): void
    {
        $this->pdfWhitespaces = $pdfWhitespaces;
    }

    public function getPdfWhitespacesRegex(): string
    {
        return $this->pdfWhitespacesRegex;
    }

    public function setPdfWhitespacesRegex(string $pdfWhitespacesRegex): void
    {
        $this->pdfWhitespacesRegex = $pdfWhitespacesRegex;
    }
}

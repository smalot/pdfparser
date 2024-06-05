<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 *
 * @date    2017-01-03
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

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Font;

/**
 * Class ElementString
 */
class ElementString extends Element
{
    public function __construct($value)
    {
        parent::__construct($value, null);
    }

    public function equals($value): bool
    {
        return $value == $this->value;
    }

    /**
     * Part of parsing process to handle escaped characters.
     * Note, most parameters are passed by reference.
     *
     * Further information in PDF specification (page 53):
     * https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/pdfreference1.7old.pdf
     */
    private static function handleEscapedCharacters(string &$name, int &$position, string &$processedName, string $char): void
    {
        // escaped chars
        $nextChar = substr($name, 0, 1);
        switch ($nextChar) {
            // end-of-line markers (CR, LF, CRLF) should be ignored
            case "\r":
            case "\n":
                preg_match('/^\\r?\\n?/', $name, $matches);
                $name = substr($name, \strlen($matches[0]));
                $position += \strlen($matches[0]);
                break;
                // process LF, CR, HT, BS, FF
            case 'n':
            case 't':
            case 'r':
            case 'b':
            case 'f':
                $processedName .= stripcslashes('\\'.$nextChar);
                $name = substr($name, 1);
                ++$position;
                break;
                // decode escaped parentheses and backslash
            case '(':
            case ')':
            case '\\':
            case ' ': // TODO: this should probably be removed - kept for compatibility
                $processedName .= $nextChar;
                $name = substr($name, 1);
                ++$position;
                break;
                // TODO: process octal encoding (but it is also processed later)
                // keep backslash in other cases
            default:
                $processedName .= $char;
        }
    }

    /**
     * @return bool|ElementString
     */
    public static function parse(string $content, ?Document $document = null, int &$offset = 0)
    {
        if (preg_match('/^\s*\((?P<name>.*)/s', $content, $match)) {
            $name = $match['name'];

            $delimiterCount = 0;
            $position = 0;
            $processedName = '';
            do {
                $char = substr($name, 0, 1);
                $name = substr($name, 1);
                ++$position;
                switch ($char) {
                    // matched delimiters should be treated as part of string
                    case '(':
                        $processedName .= $char;
                        ++$delimiterCount;
                        break;
                    case ')':
                        if (0 === $delimiterCount) {
                            $name = substr($name, 1);
                            break 2;
                        }
                        $processedName .= $char;
                        --$delimiterCount;
                        break;
                    case '\\':
                        self::handleEscapedCharacters($name, $position, $processedName, $char);
                        break;
                    default:
                        $processedName .= $char;
                }
            } while ('' !== $name);

            $offset += strpos($content, '(') + 1 + $position;

            $name = $processedName;

            // Decode string.
            $name = Font::decodeOctal($name);
            $name = Font::decodeEntities($name);
            $name = Font::decodeHexadecimal($name, false);
            $name = Font::decodeUnicode($name);

            return new self($name);
        }

        return false;
    }
}

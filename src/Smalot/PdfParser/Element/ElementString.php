<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-3.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2014 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 *
 */

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Font;

/**
 * Class ElementString
 *
 * @package Smalot\PdfParser\Element
 */
class ElementString extends Element
{
    /**
     * @param string   $value
     * @param Document $document
     */
    public function __construct($value, Document $document = null)
    {
        parent::__construct($value, null);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        return $value == $this->value;
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementString
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*\((?P<name>.*)/s', $content, $match)) {
            $name = $match['name'];

            // Find next ')' not escaped.
            $cur_start_text = $start_search_end = 0;
            while (($cur_start_pos = strpos($name, ')', $start_search_end)) !== false) {
                $cur_extract = substr($name, $cur_start_text, $cur_start_pos - $cur_start_text);
                preg_match('/(?P<escape>[\\\]*)$/s', $cur_extract, $match);
                if (!(strlen($match['escape']) % 2)) {
                    break;
                }
                $start_search_end = $cur_start_pos + 1;
            }

            // Extract string.
            $name   = substr($name, 0, $cur_start_pos);
            $offset += strpos($content, '(') + $cur_start_pos + 2; // 2 for '(' and ')'
            $name   = str_replace(
                array('\\\\', '\\ ', '\\/', '\(', '\)', '\n', '\r', '\t'),
                array('\\',   ' ',   '/',   '(',  ')',  "\n", "\r", "\t"),
                $name
            );

            // Decode string.
            $name = Font::decodeOctal($name);
            $name = Font::decodeEntities($name);
            $name = Font::decodeHexadecimal($name, false);
            $name = Font::decodeUnicode($name);

            return new self($name, $document);
        }

        return false;
    }
}

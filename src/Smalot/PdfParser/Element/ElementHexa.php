<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
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

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Document;

/**
 * Class ElementHexa
 */
class ElementHexa extends ElementString
{
    /**
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementHexa|ElementDate
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*\<(?P<name>[A-F0-9]+)\>/is', $content, $match)) {
            $name = $match['name'];
            $offset += strpos($content, '<'.$name) + \strlen($name) + 2; // 1 for '>'
            // repackage string as standard
            $name = '('.self::decode($name, $document).')';
            $element = false;

            if (!($element = ElementDate::parse($name, $document))) {
                $element = ElementString::parse($name, $document);
            }

            return $element;
        }

        return false;
    }

    /**
     * @param string   $value
     * @param Document $document
     */
    public static function decode($value, Document $document = null)
    {
        $text = '';
        $length = \strlen($value);

        if ('00' === substr($value, 0, 2)) {
            for ($i = 0; $i < $length; $i += 4) {
                $hex = substr($value, $i, 4);
                $text .= '&#'.str_pad(hexdec($hex), 4, '0', \STR_PAD_LEFT).';';
            }
        } else {
            for ($i = 0; $i < $length; $i += 2) {
                $hex = substr($value, $i, 2);
                $text .= \chr(hexdec($hex));
            }
        }

        $text = html_entity_decode($text, \ENT_NOQUOTES, 'UTF-8');

        return $text;
    }
}

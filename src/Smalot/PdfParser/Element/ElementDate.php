<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
 * @license LGPLv3
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHPi, extraction oriented.
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
 *
 */

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Document;

/**
 * Class ElementDate
 *
 * @package Smalot\PdfParser\Element
 */
class ElementDate extends ElementString
{
    /**
     * @var array
     */
    protected static $formats = array(
        4  => 'Y',
        6  => 'Ym',
        8  => 'Ymd',
        10 => 'YmdH',
        12 => 'YmdHi',
        14 => 'YmdHis',
        15 => 'YmdHise',
        17 => 'YmdHisO',
        18 => 'YmdHisO',
        19 => 'YmdHisO',
    );

    /**
     * @var string
     */
    protected $format = 'c';

    /**
     * @param \DateTime $value
     * @param Document  $document
     */
    public function __construct($value, Document $document = null)
    {
        if (!($value instanceof \DateTime)) {
            throw new \Exception('DateTime required.');
        }

        parent::__construct($value, null);
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        if ($value instanceof \DateTime) {
            $timestamp = $value->getTimeStamp();
        } else {
            $timestamp = strtotime($value);
        }

        return ($timestamp == $this->value->getTimeStamp());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)($this->value->format($this->format));
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementDate
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*\(D\:(?P<name>.*?)\)/s', $content, $match)) {
            $name = $match['name'];
            $name = str_replace("'", '', $name);
            $date = false;

            // Smallest format : Y
            // Full format     : YmdHisP
            if (preg_match('/^\d{4}(\d{2}(\d{2}(\d{2}(\d{2}(\d{2}(Z(\d{2,4})?|[\+-]?\d{2}(\d{2})?)?)?)?)?)?)?$/', $name)) {
                if ($pos = strpos($name, 'Z')) {
                    $name = substr($name, 0, $pos + 1);
                } elseif (strlen($name) == 18 && preg_match('/[^\+-]0000$/', $name)) {
                    $name = substr($name, 0, -4) . '+0000';
                }

                $format = self::$formats[strlen($name)];
                $date   = \DateTime::createFromFormat($format, $name, new \DateTimeZone('UTC'));
            } else {
                // special cases
                if (preg_match('/^\d{1,2}-\d{1,2}-\d{4},?\s+\d{2}:\d{2}:\d{2}[\+-]\d{4}$/', $name)) {
                    $name   = str_replace(',', '', $name);
                    $format = 'n-j-Y H:i:sO';
                    $date   = \DateTime::createFromFormat($format, $name, new \DateTimeZone('UTC'));
                }
            }

            if (!$date) {
                return false;
            }

            $offset += strpos($content, '(D:') + strlen($match['name']) + 4; // 1 for '(D:' and ')'
            $element = new self($date, $document);

            return $element;
        }

        return false;
    }
}

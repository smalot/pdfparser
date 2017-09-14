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
 *
 */

namespace Smalot\PdfParser;

use Smalot\PdfParser\Element\ElementNumeric;

/**
 * Class Encoding
 *
 * @package Smalot\PdfParser
 */
class Encoding extends PDFObject
{
    /**
     * @var array
     */
    protected $encoding;

    /**
     * @var array
     */
    protected $differences;

    /**
     * @var array
     */
    protected $mapping;

    /**
     *
     */
    public function init()
    {
        $this->mapping     = array();
        $this->differences = array();
        $this->encoding    = null;

        if ($this->has('BaseEncoding')) {
            // Load reference table charset.
            $baseEncoding = preg_replace('/[^A-Z0-9]/is', '', $this->get('BaseEncoding')->getContent());
            $className    = '\\Smalot\\PdfParser\\Encoding\\' . $baseEncoding;

            if (class_exists($className)) {
                $class = new $className();
                $this->encoding = $class->getTranslations();
            } else {
                throw new \Exception('Missing encoding data for: "' . $baseEncoding . '".');
            }

            // Build table including differences.
            $differences = $this->get('Differences')->getContent();
            $code        = 0;

            if (!is_array($differences)) {
                return;
            }

            foreach ($differences as $difference) {
                /** @var ElementNumeric $difference */
                if ($difference instanceof ElementNumeric) {
                    $code = $difference->getContent();
                    continue;
                }

                // ElementName
                if (is_object($difference)) {
                    $this->differences[$code] = $difference->getContent();
                } else {
                    $this->differences[$code] = $difference;
                }

                // For the next char.
                $code++;
            }

            // Build final mapping (custom => standard).
            $table = array_flip(array_reverse($this->encoding, true));

            foreach ($this->differences as $code => $difference) {
                /** @var string $difference */
                $this->mapping[$code] = (isset($table[$difference]) ? $table[$difference] : Font::MISSING);
            }
        }
    }

    /**
     * @return array
     */
    public function getDetails($deep = true)
    {
        $details = array();

        $details['BaseEncoding'] = ($this->has('BaseEncoding') ? (string)$this->get('BaseEncoding') : 'Ansi');
        $details['Differences']  = ($this->has('Differences') ? (string)$this->get('Differences') : '');

        $details += parent::getDetails($deep);

        return $details;
    }

    /**
     * @param int $char
     *
     * @return int
     */
    public function translateChar($dec)
    {
        if (isset($this->mapping[$dec])) {
            $dec = $this->mapping[$dec];
        }

        return $dec;
    }
}

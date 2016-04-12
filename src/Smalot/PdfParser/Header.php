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

namespace Smalot\PdfParser;

use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementMissing;
use Smalot\PdfParser\Element\ElementStruct;
use Smalot\PdfParser\Element\ElementXRef;

/**
 * Class Header
 *
 * @package Smalot\PdfParser
 */
class Header
{
    /**
     * @var Document
     */
    protected $document = null;

    /**
     * @var Element[]
     */
    protected $elements = null;

    /**
     * @param Element[] $elements   List of elements.
     * @param Document  $document   Document.
     */
    public function __construct($elements = array(), Document $document = null)
    {
        $this->elements = $elements;
        $this->document = $document;
    }

    /**
     * Returns all elements.
     *
     * @return mixed
     */
    public function getElements()
    {
        foreach ($this->elements as $name => $element) {
            $this->resolveXRef($name);
        }

        return $this->elements;
    }

    /**
     * Used only for debug.
     *
     * @return array
     */
    public function getElementTypes()
    {
        $types = array();

        foreach ($this->elements as $key => $element) {
            $types[$key] = get_class($element);
        }

        return $types;
    }

    /**
     * @param bool $deep
     *
     * @return array
     */
    public function getDetails($deep = true)
    {
        $values   = array();
        $elements = $this->getElements();

        foreach ($elements as $key => $element) {
            if ($element instanceof Header && $deep) {
                $values[$key] = $element->getDetails($deep);
            } elseif ($element instanceof Object && $deep) {
                $values[$key] = $element->getDetails(false);
            } elseif ($element instanceof ElementArray) {
                if ($deep) {
                    $values[$key] = $element->getDetails();
                }
            } elseif ($element instanceof Element) {
                $values[$key] = (string) $element;
            }
        }

        return $values;
    }

    /**
     * Indicate if an element name is available in header.
     *
     * @param string $name The name of the element
     *
     * @return bool
     */
    public function has($name)
    {
        if (array_key_exists($name, $this->elements)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $name
     *
     * @return Element|Object
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->elements)) {
            return $this->resolveXRef($name);
        }

        return new ElementMissing(null, null);
    }

    /**
     * Resolve XRef to object.
     *
     * @param string $name
     *
     * @return Element|Object
     * @throws \Exception
     */
    protected function resolveXRef($name)
    {
        if (($obj = $this->elements[$name]) instanceof ElementXRef && !is_null($this->document)) {
            /** @var ElementXRef $obj */
            $object = $this->document->getObjectById($obj->getId());

            if (is_null($object)) {
                return null;
            }

            // Update elements list for future calls.
            $this->elements[$name] = $object;
        }

        return $this->elements[$name];
    }

    /**
     * @param string   $content  The content to parse
     * @param Document $document The document
     * @param int      $position The new position of the cursor after parsing
     *
     * @return Header
     */
    public static function parse($content, Document $document, &$position = 0)
    {
        /** @var Header $header */
        if (substr(trim($content), 0, 2) == '<<') {
            $header = ElementStruct::parse($content, $document, $position);
        } else {
            $elements = ElementArray::parse($content, $document, $position);
            if ($elements) {
                $header = new self($elements->getRawContent(), null);//$document);
            } else {
                $header = new self(array(), $document);
            }
        }

        if ($header) {
            return $header;
        } else {
            // Build an empty header.
            return new self(array(), $document);
        }
    }
}

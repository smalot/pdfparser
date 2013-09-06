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
 */

namespace Smalot\PdfParser;

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
     * @param Element[] $struct   List of elements.
     * @param Document  $document Document.
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
                throw new \Exception('Missing object reference #' . $obj->getId() . '.');
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
        $header = ElementStruct::parse($content, $document, $position);

        if ($header) {
            return $header;
        } else {
            // Build an empty header.
            return new self(array(), $document);
        }
    }
}

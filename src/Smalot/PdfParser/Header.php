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
     * @param Element[] $struct
     * @param Document  $document
     */
    public function __construct($elements = array(), Document $document = null)
    {
        $this->elements = $elements;

        $this->document = $document;
    }

    /**
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
     * @param $name
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
     * @param string $name
     *
     * @return Element|Object
     * @throws \Exception
     */
    protected function resolveXRef($name)
    {
        if (($obj = $this->elements[$name]) instanceof ElementXRef && !is_null($this->document)) {
            $object = $this->document->getObjectById($obj->getId());

            if (is_null($object)) {
                throw new \Exception('Missing object reference #' . $obj->getId() . '.');
            }

            $this->elements[$name] = $object;
        }

        return $this->elements[$name];
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $position
     *
     * @return null|Header
     */
    public static function parse($content, Document $document, &$position = 0)
    {
        // ElementStruct::parse returns an header
        $header = ElementStruct::parse($content, $document, $position);

        if ($header) {
            return $header;
        } else {
            return new self(array(), $document);
        }
    }
}

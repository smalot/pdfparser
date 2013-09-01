<?php

/**
 * @file
 * This file is part of the PdfParser library.
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
use Smalot\PdfParser\Element\ElementXRef;

/**
 * Class Header
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
     * @param Element[] $elements
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
     * @param $name
     *
     * @return bool
     */
    public function has($name)
    {
        if (array_key_exists($name, $this->elements) && !($this->elements[$name] instanceof ElementMissing)) {
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

        return ($this->elements[$name] = new ElementMissing(null, null));
    }

    /**
     * @param string $name
     *
     * @return Element|Object
     */
    protected function resolveXRef($name)
    {
        if (($obj = $this->elements[$name]) instanceof ElementXRef and !is_null($this->document)) {
            $this->elements[$name] = $this->document->getObjectById($obj->getId());
        }

        return $this->elements[$name];
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $position
     *
     * @return Header
     */
    public static function parse($content, Document $document, &$position = 0)
    {
        $elements = Element::parse($content, $document, $position);

        return new self($elements, $document);
    }
}

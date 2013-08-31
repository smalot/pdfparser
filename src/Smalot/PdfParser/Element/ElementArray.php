<?php

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Object;

/**
 * Class ElementArray
 * @package Smalot\PdfParser\Element
 */
class ElementArray extends Element
{
    /**
     * @param string   $value
     * @param Document $document
     */
    public function __construct($value, Document $document = null)
    {
        parent::__construct($value, $document);
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        foreach ($this->value as $name => $element) {
            $this->resolveXRef($name);
        }

        return parent::getContent();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(',', $this->value);
    }

    /**
     * @param string $name
     *
     * @return Element|Object
     */
    protected function resolveXRef($name)
    {
        if (($obj = $this->value[$name]) instanceof ElementXRef) {
            /** @var Object $obj */
            $obj                = $this->document->getObjectById($obj->getId());
            $this->value[$name] = $obj;
        }

        return $this->value[$name];
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementArray
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*\[(?<array>.*)/is', $content, $match)) {
            preg_match_all('/(.*?)(\[|\])/s', trim($content), $matches);

            $level = 0;
            $sub   = '';
            foreach ($matches[0] as $part) {
                $sub .= $part;
                $level += (strpos($part, '[') !== false ? 1 : -1);
                if ($level <= 0) {
                    break;
                }
            }

            $offset = strpos($content, '[') + strlen(rtrim($sub));

            // Removes 1 level [ and ].
            $sub = substr(trim($sub), 1, -1);

            $values = Element::parse($sub, $document, $position, true);

            return new self($values, $document);
        }

        return false;
    }
}

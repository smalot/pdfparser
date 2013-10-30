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

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Object;

/**
 * Class ElementArray
 *
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
     * @param bool $deep
     *
     * @return array
     */
    public function getDetails($deep = true)
    {
        $values   = array();
        $elements = $this->getContent();

        foreach ($elements as $key => $element) {
            if ($element instanceof Header && $deep) {
                $values[$key] = $element->getDetails($deep);
            } elseif ($element instanceof Object && $deep) {
                $values[$key] = $element->getDetails(false);
            } elseif ($element instanceof ElementArray) {
                if ($deep) {
                    $values[$key] = $element->getDetails();
                }
            } elseif ($element instanceof Element && !($element instanceof ElementArray)) {
                $values[$key] = $element->getContent();
            }
        }

        return $values;
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

            // Removes 1 level [ and ].
            $sub        = substr(trim($sub), 1, -1);
            $sub_offset = 0;
            $values     = Element::parse($sub, $document, $sub_offset, true);

            $offset += strpos($content, '[') + 1;
            // Find next ']' position
            $offset += strlen($sub) + 1;

            return new self($values, $document);
        }

        return false;
    }
}

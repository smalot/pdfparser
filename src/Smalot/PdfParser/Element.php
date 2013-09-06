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

use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementBoolean;
use Smalot\PdfParser\Element\ElementDate;
use Smalot\PdfParser\Element\ElementHexa;
use Smalot\PdfParser\Element\ElementName;
use Smalot\PdfParser\Element\ElementNull;
use Smalot\PdfParser\Element\ElementNumeric;
use Smalot\PdfParser\Element\ElementString;
use Smalot\PdfParser\Element\ElementStruct;
use Smalot\PdfParser\Element\ElementXRef;

/**
 * Class Element
 *
 * @package Smalot\PdfParser
 */
class Element
{
    /**
     * @var Document
     */
    protected $document = null;

    /**
     * @var mixed
     */
    protected $value = null;

    /**
     * @param mixed $value
     * @param Document $document
     */
    public function __construct($value, Document $document = null)
    {
        $this->value = $value;
        $this->document = $document;
    }

    /**
     *
     */
    public function init()
    {

    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        return ($value == $this->value);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($value)
    {
        if (is_array($this->value)) {
            /** @var Element $val */
            foreach ($this->value as $val) {
                if ($val->equals($value)) {
                    return true;
                }
            }

            return false;
        } else {
            return $this->equals($value);
        }
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)($this->value);
    }

    /**
     * @param string $content
     * @param Document $document
     * @param int $position
     *
     * @return array
     * @throws \Exception
     */
    public static function parse($content, Document $document = null, &$position = 0)
    {
        $args = func_get_args();
        $only_values = false;
        if (isset($args[3])) {
            $only_values = $args[3];
        }

        $position = 0;
        $content = trim($content);
        $values = array();

        do {
            $sub_content = substr($content, $position);
            $offset = 0;

            if (!$only_values) {
                if (!preg_match('/^\s*(?<name>\/[A-Z0-9\._]+)(?<value>.*)/si', $sub_content, $match)) {
                    break;
                } else {
                    $name = ltrim($match['name'], '/');
                    $value = $match['value'];
                    $position = strpos($content, $value, $position + strlen($match['name']));
                }
            } else {
                $name = count($values);
                $value = $sub_content;
            }

            if ($element = ElementName::parse($value, $document, $offset)) {
                $values[$name] = $element;
                $position += $offset;
            } elseif ($element = ElementXRef::parse($value, $document, $offset)) {
                $values[$name] = $element;
                $position += $offset;
            } elseif ($element = ElementNumeric::parse($value, $document, $offset)) {
                $values[$name] = $element;
                $position += $offset;
            } elseif ($element = ElementStruct::parse($value, $document, $offset)) {
                $values[$name] = $element;
                $position += $offset;
            } elseif ($element = ElementBoolean::parse($value, $document, $offset)) {
                $values[$name] = $element;
                $position += $offset;
            } elseif ($element = ElementNull::parse($value, $document, $offset)) {
                $values[$name] = $element;
                $position += $offset;
            } elseif ($element = ElementDate::parse($value, $document, $offset)) {
                $values[$name] = $element;
                $position += $offset;
            } elseif ($element = ElementString::parse($value, $document, $offset)) {
                $values[$name] = $element;
                $position += $offset;
            } elseif ($element = ElementHexa::parse($value, $document, $offset)) {
                $values[$name] = $element;
                $position += $offset;
            } elseif ($element = ElementArray::parse($value, $document, $offset)) {
                $values[$name] = $element;
                $position += $offset;
            } else {
                return $values;
            }
        } while ($position < strlen($content));

        return $values;
    }
}

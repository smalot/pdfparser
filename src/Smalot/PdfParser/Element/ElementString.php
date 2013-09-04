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

/**
 * Class ElementString
 *
 * @package Smalot\PdfParser\Element
 */
class ElementString extends Element
{
    /**
     * @param string   $value
     * @param Document $document
     */
    public function __construct($value, Document $document = null)
    {
        parent::__construct($value, null);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        return $value == $this->value;
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementString
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*\((?<name>.*?)\)/is', $content, $match)) {
            $name   = $match['name'];
            $offset = strpos($content, '(' . $name) + strlen($name) + 2; // 2 for '(' and ')'

            $name = self::decodeOctal($name);

            return new self($name, $document);
        }

        return false;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public static function decodeOctal($text)
    {
        $found_octal_values = array();
        preg_match_all('/\\\\([0-9]{3})/', $text, $found_octal_values);
        foreach ($found_octal_values[0] as $value) {
            $octal = substr($value, 1);
            $text  = str_replace($value, chr(octdec($octal)), $text);
        }

        return $text;
    }
}

<?php

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Document;

/**
 * Class ElementDate
 * @package Smalot\PdfParser\Element
 */
class ElementDate extends ElementString
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
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementDate
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*\((?<name>D\:.*?)\)/is', $content, $match)) {
            $name   = $match['name'];
            $offset = strpos($content, $name) + strlen($name) + 1; // 1 for ')'

            return new self($name, $document);
        }

        return false;
    }
}

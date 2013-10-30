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
use Smalot\PdfParser\Font;

/**
 * Class ElementName
 *
 * @package Smalot\PdfParser\Element
 */
class ElementName extends Element
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
     * @return bool|ElementName
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*\/(?<name>[A-Z0-9\-\+,#\.]+)/is', $content, $match)) {
            $name   = $match['name'];
            $offset += strpos($content, $name) + strlen($name);
            $name   = Font::decodeEntities($name);

            return new self($name, $document);
        }

        return false;
    }
}

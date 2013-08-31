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

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Document;

/**
 * Class ElementXRef
 * @package Smalot\PdfParser\Element
 */
class ElementXRef extends Element
{
    /**
     * @return int
     */
    public function getId()
    {
        return intval($this->getContent());
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        $id = ($value instanceof ElementXRef) ? $value->getId() : $value;

        return $this->getId() == $id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '#Obj#' . $this->getId();
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementXRef
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*(?<id>[0-9]+\s+[0-9]+\s+R)/s', $content, $match)) {
            $id     = $match['id'];
            $offset = strpos($content, $id) + strlen($id);

            return new self($id, $document);
        }

        return false;
    }
}

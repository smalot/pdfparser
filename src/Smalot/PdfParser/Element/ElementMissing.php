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
 * Class ElementMissing
 */
class ElementMissing extends Element
{
    /**
     * @param string   $value
     * @param Document $document
     */
    public function __construct($value, Document $document = null)
    {
        parent::__construct(null, null);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        return false;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($value)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function getContent()
    {
        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}

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

namespace Smalot\PdfParser\XObject;

use Smalot\PdfParser\Header;
use Smalot\PdfParser\Object;
use Smalot\PdfParser\Page;

/**
 * Class Form
 *
 * @package Smalot\PdfParser\XObject
 */
class Form extends Page
{
    /**
     * @param Page
     *
     * @return string
     */
    public function getText(Page $page = null)
    {
        $header   = new Header(array(), $this->document);
        $contents = new Object($this->document, $header, $this->content);

        return $contents->getText($this);
    }
}

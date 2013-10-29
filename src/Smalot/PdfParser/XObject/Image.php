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

use Smalot\PdfParser\Object;
use Smalot\PdfParser\Page;

/**
 * Class Image
 *
 * @package Smalot\PdfParser\XObject
 */
class Image extends Object
{
    /**
     * @param Page
     *
     * @return string
     */
    public function getText(Page $page = null)
    {
        return '';
    }
}

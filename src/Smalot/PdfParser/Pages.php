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

namespace Smalot\PdfParser;

/**
 * Class Pages
 * @package Smalot\PdfParser
 */
class Pages extends Object
{
    public function getPages($deep = false)
    {
        if ($this->has('Kids')) {
            if (!$deep) {
                return $this->get('Kids')->getContent();
            } else {
                $kids  = $this->get('Kids')->getContent();
                $pages = array();

                foreach ($kids as $kid) {
                    if ($kid instanceof Pages) {
                        $pages = array_merge($pages, $kid->getPages(true));
                    } else {
                        $pages[] = $kid;
                    }
                }

                return $pages;
            }
        }

        return array();
    }
}

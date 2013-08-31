<?php

namespace Smalot\PdfParser;

/**
 * Class Pages
 * @package PdfParser
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

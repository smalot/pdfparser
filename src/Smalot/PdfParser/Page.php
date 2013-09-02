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
 * Class Page
 * @package Smalot\PdfParser
 */
class Page extends Object
{
    /**
     * @var Object
     */
    protected $resources = null;

    /**
     * @var Object
     */
    protected $contents = null;

    /**
     * @var Font[]
     */
    protected $fonts = null;

    /**
     * @return Object
     */
    public function getResources()
    {
        if (!is_null($this->resources)) {
            return $this->resources;
        }

        $resources = $this->get('Resources');

        return ($this->resources = $resources);
    }

    /**
     * @return Object
     */
    public function getContents()
    {
        if (!is_null($this->contents)) {
            return $this->contents;
        }

        $contents = $this->get('Contents');

        return ($this->contents = $contents);
    }

    /**
     * @return Font[]
     */
    public function getFonts()
    {
        if (!is_null($this->fonts)) {
            return $this->fonts;
        }

        $resources = $this->getResources();

        if ($resources->has('Font')) {
            $fonts = $resources->get('Font')->getHeader()->getElements();
            $table = array();

            foreach ($fonts as $id => $font) {
                $id         = preg_replace('/[^0-9\.\-_]/', '', $id);
                $table[$id] = $font;
            }

            return ($this->fonts = $table);
        } else {
            return array();
        }
    }

    /**
     * @param string $id
     *
     * @return Font
     */
    public function getFont($id)
    {
        $fonts = $this->getFonts();
        $id    = preg_replace('/[^0-9\.\-_]/', '', $id);

        return $fonts[$id];
    }

    /**
     * @param Page
     *
     * @return string
     */
    public function getText(Page $page = null)
    {
        $contents = $this->getContents();

//        var_dump($contents);
//        die();

        if ($contents) {
            if (is_array($contents)) {
                $text = '';

                foreach ($contents as $content) {
                    $text .= $content->getText($this) . "\n";
                }

                return $text;
            } else {
                return $contents->getText($this);
            }
        } else {
            return null;
        }
    }
}

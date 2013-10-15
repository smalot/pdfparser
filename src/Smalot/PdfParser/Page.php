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

namespace Smalot\PdfParser;

use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementMissing;

/**
 * Class Page
 *
 * @package Smalot\PdfParser
 */
class Page extends Object
{
    /**
     * @var Object
     */
//    protected $resources = null;

    /**
     * @var Object
     */
//    protected $contents = null;

    /**
     * @var Font[]
     */
    protected $fonts = null;

    /**
     * @var Object[]
     */
    protected $xobjects = null;

    /**
     * @return Object
     */
    /*public function getResources()
    {
        if (!is_null($this->resources)) {
            return $this->resources;
        }

        $resources = $this->get('Resources');

        return ($this->resources = $resources);
    }*/

    /**
     * @return Object
     */
    /*public function getContents()
    {
        if (!is_null($this->contents)) {
            return $this->contents;
        }

        $contents = $this->get('Contents');

        return ($this->contents = $contents);
    }*/

    /**
     * @return Font[]
     */
    public function getFonts()
    {
        if (!is_null($this->fonts)) {
            return $this->fonts;
        }

        $resources = $this->get('Resources');

        if ($resources->has('Font')) {

            if ($resources->get('Font') instanceof Header) {
                $fonts = $resources->get('Font')->getElements();
            } else {
                $fonts = $resources->get('Font')->getHeader()->getElements();
            }

            $table = array();

            foreach ($fonts as $id => $font) {
                $table[$id] = $font;

                // Store too on cleaned id value (only numeric)
                $id = preg_replace('/[^0-9\.\-_]/', '', $id);
                if ($id != '') {
                    $table[$id] = $font;
                }
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

        if (isset($fonts[$id])) {
            return $fonts[$id];
        } else {
            $id = preg_replace('/[^0-9\.\-_]/', '', $id);

            if (isset($fonts[$id])) {
                return $fonts[$id];
            } else {
                return null;
            }
        }
    }

    /**
     * Support for XObject
     *
     * @return Object[]
     */
    public function getXObjects()
    {
        if (!is_null($this->xobjects)) {
            return $this->xobjects;
        }

        $resources = $this->get('Resources');

        if ($resources->has('XObject')) {

            if ($resources->get('XObject') instanceof Header) {
                $xobjects = $resources->get('XObject')->getElements();
            } else {
                $xobjects = $resources->get('XObject')->getHeader()->getElements();
            }

            $table = array();

            foreach ($xobjects as $id => $xobject) {
                $table[$id] = $xobject;

                // Store too on cleaned id value (only numeric)
                $id = preg_replace('/[^0-9\.\-_]/', '', $id);
                if ($id != '') {
                    $table[$id] = $xobject;
                }
            }

            return ($this->xobjects = $table);
        } else {
            return array();
        }
    }

    /**
     * @param string $id
     *
     * @return Object
     */
    public function getXObject($id)
    {
        $xobjects = $this->getXObjects();

        if (isset($xobjects[$id])) {
            return $xobjects[$id];
        } else {
            $id = preg_replace('/[^0-9\.\-_]/', '', $id);

            if (isset($xobjects[$id])) {
                return $xobjects[$id];
            } else {
                return null;
            }
        }
    }

    /**
     * @param Page
     *
     * @return string
     */
    public function getText(Page $page = null)
    {
        $contents = $this->get('Contents');

        if ($contents) {
            if ($contents instanceof ElementArray) {
                // Create a virtual global content.
                $new_content = '';

                foreach ($contents->getContent() as $content) {
                    $new_content .= $content->getContent() . "\n";
                }

                $header   = new Header(array(), $this->document);
                $contents = new Object($this->document, $header, $new_content);

                return $contents->getText($this);
            } elseif (!($contents instanceof ElementMissing)) {
                return $contents->getText($this);
            }
        }

        return '';
    }
}

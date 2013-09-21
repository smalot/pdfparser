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

use Smalot\PdfParser\Element\ElementBoolean;
use Smalot\PdfParser\Element\ElementDate;
use Smalot\PdfParser\Element\ElementNumeric;
use Smalot\PdfParser\Element\ElementString;

/**
 * Technical references :
 * - http://www.mactech.com/articles/mactech/Vol.15/15.09/PDFIntro/index.html
 * - http://framework.zend.com/issues/secure/attachment/12512/Pdf.php
 * - http://www.php.net/manual/en/ref.pdf.php#74211
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/ISOLatin1Encoding.pm
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/ISOLatin9Encoding.pm
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/StandardEncoding.pm
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/WinAnsiEncoding.pm
 *
 * Class Document
 *
 * @package Smalot\PdfParser
 */
class Document
{
    /**
     * @var Object[]
     */
    protected $objects = array();

    /**
     * @var array
     */
    protected $dictionary = array();

    /**
     * @var Header
     */
    protected $trailer = null;

    /**
     * @var array
     */
    protected $details = null;

    /**
     *
     */
    public function __construct()
    {
        $this->trailer = new Header(array(), $this);
    }

    /**
     *
     */
    public function init()
    {
        $this->buildDictionary();

        $this->buildDetails();

        // Propagate init to objects.
        foreach ($this->objects as $object) {
            $object->init();
        }
    }

    /**
     * Build dictionary based on type header field.
     */
    protected function buildDictionary()
    {
        // Build dictionary.
        $this->dictionary = array();

        foreach ($this->objects as $id => $object) {
            $type = $object->getHeader()->get('Type')->getContent();

            if (!empty($type)) {
                $this->dictionary[$type][$id] = $id;
            }
        }
    }

    /**
     * Build details array.
     */
    protected function buildDetails()
    {
        // Build details array.
        $details = array();

        // Extract document info
        if ($this->trailer->has('Info')) {
            /** @var Object $info */
            $info     = $this->trailer->get('Info');
            $elements = $info->getHeader()->getElements();

            foreach ($elements as $name => $element) {
                if ($element instanceof ElementDate) {
                    $element->setFormat('c');
                }
                $details[$name] = (string)$element;
            }
        }

        // Retrieve the page count
        try {
            $pages            = $this->getPages();
            $details['Pages'] = count($pages);
        } catch (\Exception $e) {
            $details['Pages'] = 0;
        }

        $this->details = $details;
    }

    /**
     * @return array
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    /**
     * @param Object[] $objects
 */
    public function setObjects($objects = array())
    {
        $this->objects = (array)$objects;

        $this->init();
    }

    /**
     * @return Object[]
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param string $id
     *
     * @return Object
     */
    public function getObjectById($id)
    {
        if (isset($this->objects[$id])) {
            return $this->objects[$id];
        } else {
            return null;
        }
    }

    /**
     * @param string $type
     * @param string $subtype
     *
     * @return Object[]
     */
    public function getObjectsByType($type, $subtype = null)
    {
        $objects = array();

        foreach ($this->objects as $id => $object) {
            if ($object->getHeader()->get('Type') == $type &&
                (is_null($subtype) || $object->getHeader()->get('Subtype') == $subtype)
            ) {
                $objects[$id] = $object;
            }
        }

        return $objects;
    }

    /**
     * @return \Object[]
     */
    public function getFonts()
    {
        return $this->getObjectsByType('Font');
    }

    /**
     * @return Page[]
     * @throws \Exception
     */
    public function getPages()
    {
        if (isset($this->dictionary['Catalog'])) {
            // Search for catalog to list pages.
            $id = reset($this->dictionary['Catalog']);

            /** @var Pages $object */
            $object = $this->objects[$id]->get('Pages');
            $pages  = $object->getPages(true);

            return $pages;
        } elseif (isset($this->dictionary['Pages'])) {
            // Search for pages to list kids.
            $pages = array();

            /** @var Pages[] $objects */
            $objects = $this->getObjectsByType('Pages');
            foreach ($objects as $object) {
                $pages = array_merge($pages, $object->getPages(true));
            }

            return $pages;
        } elseif (isset($this->dictionary['Page'])) {
            // Search for 'page' (unordered pages).
            $pages = $this->getObjectsByType('Page');

            return array_values($pages);
        } else {
            throw new \Exception('Missing catalog.');
        }
    }

    /**
     * @param Page $page
     *
     * @return string
     */
    public function getText(Page $page = null)
    {
        $texts = array();
        $pages = $this->getPages();

        foreach ($pages as $index => $page) {
            if ($text = trim($page->getText())) {
                $texts[] = $text;
            }
        }

        return implode("\n\n", $texts);
    }

    /**
     * @param Header $header
     */
    public function setTrailer(Header $trailer)
    {
        $this->trailer = $trailer;
    }

    /**
     * @param array $details
     */
    public function setDetails(array $details)
    {
        $this->details = $details;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }
}

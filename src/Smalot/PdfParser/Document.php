<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
 *
 * @license LGPLv3
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2017 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 */

namespace Smalot\PdfParser;

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
 */
class Document
{
    /**
     * @var PDFObject[]
     */
    protected $objects = [];

    /**
     * @var array
     */
    protected $dictionary = [];

    /**
     * @var Header
     */
    protected $trailer = null;

    /**
     * @var array
     */
    protected $details = null;

    public function __construct()
    {
        $this->trailer = new Header([], $this);
    }

    public function init()
    {
        $this->buildDictionary();

        $this->buildDetails();

        // Propagate init to objects.
        foreach ($this->objects as $object) {
            $object->getHeader()->init();
            $object->init();
        }
    }

    /**
     * Build dictionary based on type header field.
     */
    protected function buildDictionary()
    {
        // Build dictionary.
        $this->dictionary = [];

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
        $details = [];

        // Extract document info
        if ($this->trailer->has('Info')) {
            /** @var PDFObject $info */
            $info = $this->trailer->get('Info');
            // This could be an ElementMissing object, so we need to check for
            // the getHeader method first.
            if (null !== $info && method_exists($info, 'getHeader')) {
                $details = $info->getHeader()->getDetails();
            }
        }

        // Retrieve the page count
        try {
            $pages = $this->getPages();
            $details['Pages'] = \count($pages);
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
     * @param PDFObject[] $objects
     */
    public function setObjects($objects = [])
    {
        $this->objects = (array) $objects;

        $this->init();
    }

    /**
     * @return PDFObject[]
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param string $id
     *
     * @return PDFObject|Font|Page|Element|null
     */
    public function getObjectById($id)
    {
        if (isset($this->objects[$id])) {
            return $this->objects[$id];
        }

        return null;
    }

    /**
     * @param string $type
     * @param string $subtype
     *
     * @return array
     */
    public function getObjectsByType($type, $subtype = null)
    {
        $objects = [];

        foreach ($this->objects as $id => $object) {
            if ($object->getHeader()->get('Type') == $type &&
                (null === $subtype || $object->getHeader()->get('Subtype') == $subtype)
            ) {
                $objects[$id] = $object;
            }
        }

        return $objects;
    }

    /**
     * @return PDFObject[]
     */
    public function getFonts()
    {
        return $this->getObjectsByType('Font');
    }

    /**
     * @return Page[]
     *
     * @throws \Exception
     */
    public function getPages()
    {
        if (isset($this->dictionary['Catalog'])) {
            // Search for catalog to list pages.
            $id = reset($this->dictionary['Catalog']);

            /** @var Pages $object */
            $object = $this->objects[$id]->get('Pages');
            if (method_exists($object, 'getPages')) {
                return $object->getPages(true);
            }
        }

        if (isset($this->dictionary['Pages'])) {
            // Search for pages to list kids.
            $pages = [];

            /** @var Pages[] $objects */
            $objects = $this->getObjectsByType('Pages');
            foreach ($objects as $object) {
                $pages = array_merge($pages, $object->getPages(true));
            }

            return $pages;
        }

        if (isset($this->dictionary['Page'])) {
            // Search for 'page' (unordered pages).
            $pages = $this->getObjectsByType('Page');

            return array_values($pages);
        }

        throw new \Exception('Missing catalog.');
    }

    /**
     * @param Page $page
     *
     * @return string
     */
    public function getText(Page $page = null)
    {
        $texts = [];
        $pages = $this->getPages();

        foreach ($pages as $index => $page) {
            /**
             * In some cases, the $page variable may be null.
             */
            if (null === $page) {
                continue;
            }
            if ($text = trim($page->getText())) {
                $texts[] = $text;
            }
        }

        return implode("\n\n", $texts);
    }

    /**
     * @return Header
     */
    public function getTrailer()
    {
        return $this->trailer;
    }

    public function setTrailer(Header $trailer)
    {
        $this->trailer = $trailer;
    }

    /**
     * @return array
     */
    public function getDetails($deep = true)
    {
        return $this->details;
    }
}

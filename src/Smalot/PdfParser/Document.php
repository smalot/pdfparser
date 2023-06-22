<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 *
 * @date    2017-01-03
 *
 * @license LGPLv3
 *
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
    protected $trailer;

    /**
     * @var Metadata
     */
    protected $metadata = [];

    /**
     * @var array
     */
    protected $details;

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
            // Cache objects by type and subtype
            $type = $object->getHeader()->get('Type')->getContent();

            if (null != $type) {
                if (!isset($this->dictionary[$type])) {
                    $this->dictionary[$type] = [
                        'all' => [],
                        'subtype' => [],
                    ];
                }

                $this->dictionary[$type]['all'][$id] = $object;

                $subtype = $object->getHeader()->get('Subtype')->getContent();
                if (null != $subtype) {
                    if (!isset($this->dictionary[$type]['subtype'][$subtype])) {
                        $this->dictionary[$type]['subtype'][$subtype] = [];
                    }
                    $this->dictionary[$type]['subtype'][$subtype][$id] = $object;
                }
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

        $details = array_merge($details, $this->metadata);

        $this->details = $details;
    }

    /**
     * Get XMP Metadata
     */
    public function getXMPMetadata(string $content)
    {
        $xml = xml_parser_create();
        xml_parser_set_option($xml, XML_OPTION_SKIP_WHITE, 1);

        if (xml_parse_into_struct($xml, $content, $values, $index)) {

            $detail = '';

            foreach ($values as $val) {
                switch ($val['tag']) {
                    case 'DC:CREATOR':
                        $detail = ($val['type'] == 'open') ? 'Author' : '';
                        break;

                    case 'DC:DESCRIPTION':
                        $detail = ($val['type'] == 'open') ? 'Description' : '';
                        break;

                    case 'DC:TITLE':
                        $detail = ($val['type'] == 'open') ? 'Title' : '';
                        break;

                    case 'DC:SUBJECT':
                        $detail = ($val['type'] == 'open') ? 'Subject' : '';
                        break;

                    case 'RDF:LI':
                        if ($detail && $val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata[$detail] = $val['value'];
                        }
                        break;

                    case 'DC:FORMAT':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['Format'] = $val['value'];
                        }
                        break;

                    case 'PDF:KEYWORDS':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['Keywords'] = $val['value'];
                        }
                        break;

                    case 'PDF:PRODUCER':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['Producer'] = $val['value'];
                        }
                        break;

                    case 'PDFX:SOURCEMODIFIED':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['SourceModified'] = $val['value'];
                        }
                        break;

                    case 'PDFX:COMPANY':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['Company'] = $val['value'];
                        }
                        break;

                    case 'XMP:CREATEDATE':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['CreationDate'] = $val['value'];
                        }
                        break;

                    case 'XMP:CREATORTOOL':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['Creator'] = $val['value'];
                        }
                        break;

                    case 'XMP:MODIFYDATE':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['ModifyDate'] = $val['value'];
                        }
                        break;

                    case 'XMP:METADATADATE':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['MetadataDate'] = $val['value'];
                        }
                        break;                

                    case 'XMPMM:DOCUMENTID':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['DocumentUUID'] = $val['value'];
                        }
                        break;                

                    case 'XMPMM:INSTANCEID':
                        if ($val['type'] == 'complete' && isset($val['value'])) {
                            $this->metadata['InstanceUUID'] = $val['value'];
                        }
                        break;                

                }
            }
        }
    }


    public function getDictionary(): array
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
     * @return PDFObject|Font|Page|Element|null
     */
    public function getObjectById(string $id)
    {
        if (isset($this->objects[$id])) {
            return $this->objects[$id];
        }

        return null;
    }

    public function hasObjectsByType(string $type, string $subtype = null): bool
    {
        return 0 < \count($this->getObjectsByType($type, $subtype));
    }

    public function getObjectsByType(string $type, string $subtype = null): array
    {
        if (!isset($this->dictionary[$type])) {
            return [];
        }

        if (null != $subtype) {
            if (!isset($this->dictionary[$type]['subtype'][$subtype])) {
                return [];
            }

            return $this->dictionary[$type]['subtype'][$subtype];
        }

        return $this->dictionary[$type]['all'];
    }

    /**
     * @return Font[]
     */
    public function getFonts()
    {
        return $this->getObjectsByType('Font');
    }

    public function getFirstFont(): ?Font
    {
        $fonts = $this->getFonts();
        if ([] === $fonts) {
            return null;
        }

        return reset($fonts);
    }

    /**
     * @return Page[]
     *
     * @throws \Exception
     */
    public function getPages()
    {
        if ($this->hasObjectsByType('Catalog')) {
            // Search for catalog to list pages.
            $catalogues = $this->getObjectsByType('Catalog');
            $catalogue = reset($catalogues);

            /** @var Pages $object */
            $object = $catalogue->get('Pages');
            if (method_exists($object, 'getPages')) {
                return $object->getPages(true);
            }
        }

        if ($this->hasObjectsByType('Pages')) {
            // Search for pages to list kids.
            $pages = [];

            /** @var Pages[] $objects */
            $objects = $this->getObjectsByType('Pages');
            foreach ($objects as $object) {
                $pages = array_merge($pages, $object->getPages(true));
            }

            return $pages;
        }

        if ($this->hasObjectsByType('Page')) {
            // Search for 'page' (unordered pages).
            $pages = $this->getObjectsByType('Page');

            return array_values($pages);
        }

        throw new \Exception('Missing catalog.');
    }

    public function getText(int $pageLimit = null): string
    {
        $texts = [];
        $pages = $this->getPages();

        // Only use the first X number of pages if $pageLimit is set and numeric.
        if (\is_int($pageLimit) && 0 < $pageLimit) {
            $pages = \array_slice($pages, 0, $pageLimit);
        }

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

    public function getTrailer(): Header
    {
        return $this->trailer;
    }

    public function setTrailer(Header $trailer)
    {
        $this->trailer = $trailer;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}

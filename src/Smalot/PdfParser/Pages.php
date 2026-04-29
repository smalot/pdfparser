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

use Smalot\PdfParser\Element\ElementArray;

/**
 * Class Pages
 */
class Pages extends PDFObject
{
    /**
     * @var array<\Smalot\PdfParser\Font>|null
     */
    protected $fonts;

    /**
     * @todo Objects other than Pages or Page might need to be treated specifically
     *       in order to get Page objects out of them.
     *
     * @see https://github.com/smalot/pdfparser/issues/331
     */
    public function getPages(bool $deep = false): array
    {
        if (!$this->has('Kids')) {
            return [];
        }

        /** @var ElementArray $kidsElement */
        $kidsElement = $this->get('Kids');

        if (!$deep) {
            return $kidsElement->getContent();
        }

        $visited = [];
        $pages = $this->collectPages($visited);

        return $this->recoverByDeclaredCount($pages);
    }

    /**
     * @param array<string, bool> $visited
     *
     * @return array<Page>
     */
    protected function collectPages(array &$visited): array
    {
        $nodeId = \function_exists('spl_object_id')
            ? (string) \spl_object_id($this)
            : \spl_object_hash($this);
        if (isset($visited[$nodeId])) {
            return [];
        }
        $visited[$nodeId] = true;

        /** @var ElementArray $kidsElement */
        $kidsElement = $this->get('Kids');

        if ($kidsElement instanceof ElementArray) {
            $kids = $kidsElement->getContent();
        } else {
            $kids = [$kidsElement];
        }

        // Prepare to apply the Pages' object's fonts to each page
        if (false === \is_array($this->fonts)) {
            $this->setupFonts();
        }
        $fontsAvailable = 0 < \count($this->fonts);
        $pages = [];

        foreach ($kids as $kid) {
            if ($kid instanceof self) {
                $pages = array_merge($pages, $kid->collectPages($visited));
            } elseif ($kid instanceof Page) {
                if ($fontsAvailable) {
                    $kid->setFonts($this->fonts);
                }
                $pages[] = $kid;
            } elseif ($kid instanceof PDFObject && $this->isRecoverablePageObject($kid)) {
                $recoveredPage = new Page($kid->getDocument(), $kid->getHeader(), $kid->getContent(), $kid->getConfig());
                if ($fontsAvailable) {
                    $recoveredPage->setFonts($this->fonts);
                }
                $pages[] = $recoveredPage;
            }
        }

        if ([] === $pages) {
            $pages = $this->recoverPagesByParentReference($fontsAvailable);
        }

        // Treat visited nodes as recursion-stack entries only:
        // this prevents loops while still allowing repeated references
        // to contribute valid page entries.
        unset($visited[$nodeId]);

        return $pages;
    }

    /**
     * @return array<Page>
     */
    protected function recoverPagesByParentReference(bool $fontsAvailable): array
    {
        $pages = [];

        foreach ($this->getDocument()->getObjects() as $object) {
            if ($object instanceof Page && $object->has('Parent') && $object->get('Parent') === $this) {
                if ($fontsAvailable) {
                    $object->setFonts($this->fonts);
                }
                $pages[] = $object;
                continue;
            }

            if (!$object instanceof PDFObject || !$this->isRecoverablePageObject($object)) {
                continue;
            }

            if ($object->get('Parent') !== $this) {
                continue;
            }

            $recoveredPage = new Page($object->getDocument(), $object->getHeader(), $object->getContent(), $object->getConfig());
            if ($fontsAvailable) {
                $recoveredPage->setFonts($this->fonts);
            }
            $pages[] = $recoveredPage;
        }

        return $pages;
    }

    protected function isRecoverablePageObject(PDFObject $object): bool
    {
        if (!$object->has('Parent')) {
            return false;
        }

        return $object->has('MediaBox') || $object->has('Contents');
    }

    /**
     * @param array<Page> $pages
     *
     * @return array<Page>
     */
    protected function deduplicatePages(array $pages): array
    {
        $seen = [];
        $deduplicated = [];

        foreach ($pages as $page) {
            $key = \function_exists('spl_object_id')
                ? (string) \spl_object_id($page)
                : \spl_object_hash($page);
            $signatureKey = $this->buildPageSignature($page);

            if (isset($seen[$key]) || isset($seen[$signatureKey])) {
                continue;
            }

            $seen[$key] = true;
            $seen[$signatureKey] = true;
            $deduplicated[] = $page;
        }

        return $deduplicated;
    }

    protected function buildPageSignature(Page $page): string
    {
        $header = $page->getHeader();
        $headerKey = \function_exists('spl_object_id')
            ? (string) \spl_object_id($header)
            : \spl_object_hash($header);

        return $headerKey.'|'.serialize($page->getContent());
    }

    /**
     * @param array<Page> $pages
     *
     * @return array<Page>
     */
    protected function recoverByDeclaredCount(array $pages): array
    {
        if (!$this->has('Count') || 0 === \count($pages)) {
            return $pages;
        }

        $countElement = $this->get('Count');
        if (!\is_object($countElement) || !method_exists($countElement, 'getContent')) {
            return $pages;
        }

        $declaredCount = (int) $countElement->getContent();
        $actualCount = \count($pages);

        if ($declaredCount <= $actualCount) {
            return $pages;
        }

        if (($declaredCount - $actualCount) > 10) {
            return $pages;
        }

        $lastPage = $pages[$actualCount - 1];
        while (\count($pages) < $declaredCount) {
            $pages[] = $lastPage;
        }

        return $pages;
    }

    /**
     * Gathers information about fonts and collects them in a list.
     *
     * @return void
     *
     * @internal
     */
    protected function setupFonts()
    {
        $resources = $this->get('Resources');

        if (method_exists($resources, 'has') && $resources->has('Font')) {
            // no fonts available, therefore stop here
            if ($resources->get('Font') instanceof Element\ElementMissing) {
                return;
            }

            if ($resources->get('Font') instanceof Header) {
                $fonts = $resources->get('Font')->getElements();
            } else {
                $fonts = $resources->get('Font')->getHeader()->getElements();
            }

            $table = [];

            foreach ($fonts as $id => $font) {
                if ($font instanceof Font) {
                    $table[$id] = $font;

                    // Store too on cleaned id value (only numeric)
                    $id = preg_replace('/[^0-9\.\-_]/', '', $id);
                    if ('' != $id) {
                        $table[$id] = $font;
                    }
                }
            }

            $this->fonts = $table;
        } else {
            $this->fonts = [];
        }
    }
}

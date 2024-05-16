<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2024-04-19
 *
 * @license LGPLv3
 *
 * @url     <https://github.com/smalot/pdfparser>
 *
 * PdfParser is a pdf library written in PHP, extraction oriented.
 * Copyright (C) 2017 - Sébastien MALOT <sebastien@malot.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.
 * If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 */

namespace PHPUnitTests\Integration;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Font;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\Pages;

/**
 * @internal only for test purposes
 */
class PagesDummy extends Pages
{
    /**
     * The purpose of this function is to bypass the tedious
     * work to setup instances which lead to a valid $fonts variable.
     *
     * @param array<\Smalot\PdfParser\Font> $fonts
     *
     * @return void
     */
    public function setFonts($fonts)
    {
        $this->fonts = $fonts;
    }
}

class PagesTest extends TestCase
{
    public function testFontsArePassedFromPagesToPage(): void
    {
        // Create mock Document, Font and Page objects
        $document = $this->createMock(Document::class);
        $font1 = new Font($document);
        $page = new Page($document);

        // Create a Header object that indicates $page is a child
        $header = new Header([
            'Kids' => new ElementArray([
                $page,
            ]),
        ], $document);

        // Use this header to create a mock Pages object
        $pages = new PagesDummy($document, $header);

        // Apply $font1 as a Font object to this Pages object;
        // setFonts is used here as part of PagesDummy, only to access
        // the protected Pages::fonts variable; it is not a method
        // available in production
        $pages->setFonts([$font1]);

        // Trigger setupFonts method in $pages
        $pages->getPages(true);

        // Since the $page object font list is empty, $font1 from Pages
        // object must be passed to the Page object
        $this->assertEquals([$font1], $page->getFonts());

        // Create a second $font2 using a different method
        $font2 = $this->createMock(Font::class);

        // Update the fonts in $pages
        $pages->setFonts([$font1, $font2]);

        // Trigger setupFonts method in $pages
        $pages->getPages(true);

        // Now that $page already has a font, updates from $pages
        // should not overwrite it
        $this->assertEquals([$font1], $page->getFonts());
    }
}

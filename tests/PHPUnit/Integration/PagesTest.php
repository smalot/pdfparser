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
    /**
     * If fonts are not stored in Page instances but in the Pages instance.
     *
     *      Pages
     *        |   `--- Font[]
     *        |
     *        |
     *        `--+ Page1 (no fonts)
     *        `--+ ...
     *
     * @see https://github.com/smalot/pdfparser/pull/698
     */
    public function testPullRequest698NoFontsSet(): void
    {
        $document = $this->createMock(Document::class);

        // Create a Page mock and tell PHPUnit that its setFonts has to be called once
        // otherwise an error is raised
        $page1 = $this->createMock(Page::class);
        $page1->expects($this->once())->method('setFonts');

        // setup header
        $header = new Header([
            'Kids' => new ElementArray([
                $page1,
            ]),
        ], $document);

        $font1 = $this->createMock(Font::class);

        // Preset fonts variable so we don't have to prepare all the
        // prerequisites manually (like creating a Ressources instance
        // with Font instances, see Pages::setupFonts())
        $pages = new PagesDummy($document, $header);
        $pages->setFonts([$font1]);

        // We expect setFonts is called on $page1, therefore no assertion here
        $pages->getPages(true);
    }

    /**
     * Dont override fonts list in a Page instance, if available.
     *
     *      Pages
     *        |   `--- Font[]           <=== less important than fonts in Page instance
     *        |
     *        |
     *        `--+ Page1
     *                  `--- Font[]     <=== must be kept
     *        `--+ ...
     *
     * @see https://github.com/smalot/pdfparser/pull/698
     */
    public function testPullRequest698DontOverride(): void
    {
        $document = $this->createMock(Document::class);

        // create a Page mock and tell PHPUnit that its setFonts has to be called once
        // otherwise an error is raised
        $font2 = new Font($document);
        $page1 = new Page($document);
        $page1->setFonts([$font2]);

        // setup header
        $header = new Header([
            'Kids' => new ElementArray([
                $page1,
            ]),
        ], $document);

        $font1 = $this->createMock(Font::class);

        $pages = new PagesDummy($document, $header);
        $pages->setFonts([$font1]);

        // Trigger setupFonts method in $pages
        $pages->getPages(true);

        // Note: 
        // $font1 and $font2 are intenionally not both of the same type.
        // One is a mock and the other one a real instance of Font.
        // This way we can simply check the return value of getFonts here.
        // If both were one of the other, we had to use a different assertation approach.
        $this->assertEquals([$font2], $page1->getFonts());
    }
}

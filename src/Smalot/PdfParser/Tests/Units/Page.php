<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-3.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2014 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 *
 */

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Page
 *
 * @package Smalot\PdfParser\Tests\Units
 */
class Page extends atoum\test
{
    public function testGetFonts()
    {
        // Document with text.
        $filename = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser   = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages    = $document->getPages();
        $page     = $pages[0];

        // the first to load data.
        $fonts = $page->getFonts();
        $this->assert->array($fonts)->isNotEmpty();
        foreach ($fonts as $font) {
            $this->assert->object($font)->isInstanceOf('\Smalot\PdfParser\Font');
        }
        // the second to use cache.
        $fonts = $page->getFonts();
        $this->assert->array($fonts)->isNotEmpty();

        // ------------------------------------------------------
        // Document without text.
        $filename = __DIR__ . '/../../../../../samples/Document3_pdfcreator_nocompressed.pdf';
        $document = $parser->parseFile($filename);
        $pages    = $document->getPages();
        $page     = $pages[0];

        // the first to load data.
        $fonts = $page->getFonts();
        $this->assert->array($fonts)->isEmpty();
        // the second to use cache.
        $fonts = $page->getFonts();
        $this->assert->array($fonts)->isEmpty();
    }

    public function testGetFont()
    {
        // Document with text.
        $filename = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser   = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages    = $document->getPages();
        $page     = $pages[0];

        // the first to load data.
        $font = $page->getFont('R7');
        $this->assert->object($font)->isInstanceOf('\Smalot\PdfParser\Font');
        $font = $page->getFont('ABC7');
        $this->assert->object($font)->isInstanceOf('\Smalot\PdfParser\Font');
    }

    public function testGetText()
    {
        // Document with text.
        $filename = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser   = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages    = $document->getPages();
        $page     = $pages[0];
        $text     = $page->getText();

        $this->assert->string($text)->hasLengthGreaterThan(150);
        $this->assert->string($text)->contains('Document title');
        $this->assert->string($text)->contains('Lorem ipsum');

        $this->assert->string($text)->contains('Calibri');
        $this->assert->string($text)->contains('Arial');
        $this->assert->string($text)->contains('Times');
        $this->assert->string($text)->contains('Courier New');
        $this->assert->string($text)->contains('Verdana');
    }
}

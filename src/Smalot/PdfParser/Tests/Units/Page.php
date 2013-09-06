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

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Page
 *
 * @package Smalot\PdfParser\Tests\Units
 */
class Page extends atoum\test
{
    public function testGetResources()
    {
        // Document with text.
        $filename = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $document = \Smalot\PdfParser\Document::parseFile($filename);
        $pages    = $document->getPages();
        $page     = $pages[0];

        // the first to load data.
        $resources = $page->getResources();
        $this->assert->object($resources)->isInstanceOf('\Smalot\PdfParser\Header');
        // the second to use cache.
        $resources = $page->getResources();
        $this->assert->object($resources)->isInstanceOf('\Smalot\PdfParser\Header');
    }

    public function testGetContents()
    {
        // Document with text.
        $filename = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $document = \Smalot\PdfParser\Document::parseFile($filename);
        $pages    = $document->getPages();
        $page     = $pages[0];

        // the first to load data.
        $contents = $page->getContents();
        $this->assert->object($contents)->isInstanceOf('\Smalot\PdfParser\Object');
        // the second to use cache.
        $contents = $page->getContents();
        $this->assert->object($contents)->isInstanceOf('\Smalot\PdfParser\Object');
    }

    public function testGetFonts()
    {
        // Document with text.
        $filename = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $document = \Smalot\PdfParser\Document::parseFile($filename);
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
        $document = \Smalot\PdfParser\Document::parseFile($filename);
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
        $document = \Smalot\PdfParser\Document::parseFile($filename);
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
        $document = \Smalot\PdfParser\Document::parseFile($filename);
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

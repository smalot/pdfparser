<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2020-06-01
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

namespace Tests\Smalot\PdfParser\Integration;

use Smalot\PdfParser\Font;
use Test\Smalot\PdfParser\TestCase;

class PageTest extends TestCase
{
    public function testGetFonts()
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];

        // the first to load data.
        $fonts = $page->getFonts();
        $this->assertTrue(0 < \count($fonts));
        foreach ($fonts as $font) {
            $this->assertTrue($font instanceof Font);
        }
        // the second to use cache.
        $fonts = $page->getFonts();
        $this->assertTrue(0 < \count($fonts));

        // ------------------------------------------------------
        // Document without text.
        $filename = $this->rootDir.'/samples/Document3_pdfcreator_nocompressed.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];

        // the first to load data.
        $fonts = $page->getFonts();
        $this->assertEquals(0, \count($fonts));
        // the second to use cache.
        $fonts = $page->getFonts();
        $this->assertEquals(0, \count($fonts));
    }

    public function testGetFont()
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];

        // the first to load data.
        $font = $page->getFont('R7');
        $this->assertTrue($font instanceof Font);

        $font = $page->getFont('ABC7');
        $this->assertTrue($font instanceof Font);
    }

    public function testGetText()
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $text = $page->getText();

        $this->assertTrue(150 < \strlen($text));
        $this->assertContains('Document title', $text);
        $this->assertContains('Lorem ipsum', $text);

        $this->assertContains('Calibri', $text);
        $this->assertContains('Arial', $text);
        $this->assertContains('Times', $text);
        $this->assertContains('Courier New', $text);
        $this->assertContains('Verdana', $text);
    }

    public function testExtractRawData()
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $extractedRawData = $page->extractRawData();
        $tmItem = $extractedRawData[1];

        $this->assertcount(172, $extractedRawData);
        $this->assertCount(3, $tmItem);

        $this->assertArrayHasKey('t', $tmItem);
        $this->assertArrayHasKey('o', $tmItem);
        $this->assertArrayHasKey('c', $tmItem);

        $this->assertContains('Tm', $tmItem['o']);
        $this->assertContains('0.999429 0 0 1 201.96 720.68', $tmItem['c']);
    }

    public function testExtractDecodedRawData()
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $extractedDecodedRawData = $page->extractDecodedRawData();
        $tmItem = $extractedDecodedRawData[1];
        $this->assertCount(172, $extractedDecodedRawData);
        $this->assertCount(3, $tmItem);

        $this->assertArrayHasKey('t', $tmItem);
        $this->assertArrayHasKey('o', $tmItem);
        $this->assertArrayHasKey('c', $tmItem);

        $this->assertContains('Tm', $tmItem['o']);
        $this->assertContains('0.999429 0 0 1 201.96 720.68', $tmItem['c']);

        $this->assertCount(3, $tmItem);
        $this->assertArrayHasKey('t', $tmItem);
        $this->assertArrayHasKey('o', $tmItem);
        $this->assertArrayHasKey('c', $tmItem);

        $tjItem = $extractedDecodedRawData[2];
        $this->assertContains('TJ', $tjItem['o']);
        $this->assertContains('(', $tjItem['c'][0]['t']);
        $this->assertContains('D', $tjItem['c'][0]['c']);
        $this->assertContains('n', $tjItem['c'][1]['t']);
        $this->assertContains('0.325008', $tjItem['c'][1]['c']);
        $this->assertContains('(', $tjItem['c'][2]['t']);
        $this->assertContains('o', $tjItem['c'][2]['c']);
    }

    public function testGetDataCommands()
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataCommands = $page->getDataCommands();
        $this->assertCount(166, $dataCommands);

        $tmItem = $dataCommands[0];
        $this->assertCount(3, $tmItem);
        $this->assertArrayHasKey('t', $tmItem);
        $this->assertArrayHasKey('o', $tmItem);
        $this->assertArrayHasKey('c', $tmItem);

        $this->assertContains('Tm', $tmItem['o']);
        $this->assertContains('0.999429 0 0 1 201.96 720.68', $tmItem['c']);
        $tjItem = $dataCommands[1];

        $this->assertCount(3, $tjItem);
        $this->assertArrayHasKey('t', $tjItem);
        $this->assertArrayHasKey('o', $tjItem);
        $this->assertArrayHasKey('c', $tjItem);

        $this->assertContains('TJ', $tjItem['o']);
        $this->assertContains('(', $tjItem['c'][0]['t']);
        $this->assertContains('D', $tjItem['c'][0]['c']);
        $this->assertContains('n', $tjItem['c'][1]['t']);
        $this->assertContains('0.325008', $tjItem['c'][1]['c']);
        $this->assertContains('(', $tjItem['c'][2]['t']);
        $this->assertContains('o', $tjItem['c'][2]['c']);
    }

    public function testGetDataTm()
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];

        $dataTm = $page->getDataTm();
        $this->assertCount(81, $dataTm);

        $item = $dataTm[0];
        $this->assertCount(2, $item);
        $this->assertCount(6, $item[0]);
        $this->assertEquals(
            [
                '0.999429',
                '0',
                '0',
                '1',
                '201.96',
                '720.68',
            ],
            $item[0]
        );

        $this->assertContains('Document title', $item[1]);
        $item = $dataTm[2];
        $this->assertEquals(
            [
                '0.999402',
                '0',
                '0',
                '1',
                '70.8',
                '673.64',
            ],
            $item[0]
        );

        $this->assertContains('Calibri : Lorem ipsum dolor sit amet, consectetur a', $item[1]);

        $item = $dataTm[80];
        $this->assertEquals(
            [
                '0.999402',
                '0',
                '0',
                '1',
                '343.003',
                '81.44',
            ],
            $item[0]
        );
        $this->assertContains('nenatis.', $item[1]);

        // ------------------------------------------------------
        // Document is a form
        $filename = $this->rootDir.'/samples/SimpleInvoiceFilledExample1.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();
        $item = $dataTm[2];
        $this->assertCount(105, $dataTm);
        $this->assertCount(2, $item);
        $this->assertCount(6, $item[0]);
        $this->assertEquals(
            [
                '1',
                '0',
                '0',
                '1',
                '167.3',
                '894.58',
            ],
            $item[0]
        );
        $this->assertContains('MyName  MyLastName', $item[1]);

        $item = $dataTm[6];
        $this->assertEquals(
            [
                '1',
                '0',
                '0',
                '1',
                '681.94',
                '877.42',
            ],
            $item[0]
        );
        $this->assertContains('1/1/2020', $item[1]);

        $item = $dataTm[8];
        $this->assertEquals(
            [
                '1',
                '0',
                '0',
                '1',
                '174.86',
                '827.14',
            ],
            $item[0]
        );
        $this->assertContains('Purchase 1', $item[1]);

        // ------------------------------------------------------
        // Document is another form of the same type
        $filename = $this->rootDir.'/samples/SimpleInvoiceFilledExample2.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();

        $item = $dataTm[2];
        $this->assertCount(105, $dataTm);
        $this->assertCount(2, $item);
        $this->assertCount(6, $item[0]);
        $this->assertEquals(
            [
                '1',
                '0',
                '0',
                '1',
                '167.3',
                '894.58',
            ],
            $item[0]
        );
        $this->assertContains("Other'sName  Other'sLastName", $item[1]);

        $item = $dataTm[6];
        $this->assertEquals(
            [
                '1',
                '0',
                '0',
                '1',
                '681.94',
                '877.42',
            ],
            $item[0]
        );
        $this->assertContains('2/2/2020', $item[1]);

        $item = $dataTm[8];
        $this->assertEquals(
            [
                '1',
                '0',
                '0',
                '1',
                '174.86',
                '827.14',
            ],
            $item[0]
        );
        $this->assertContains('Purchase 2', $item[1]);
    }

    public function testGetTextXY()
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $result = $page->getTextXY(201.96, 720.68);
        $this->assertCount(1, $result);
        $this->assertCount(2, $result[0]);
        $this->assertEquals(
            [
                '0.999429',
                '0',
                '0',
                '1',
                '201.96',
                '720.68',
            ],
            $result[0][0]
        );
        $this->assertContains('Document title', $result[0][1]);

        $result = $page->getTextXY(201, 720);
        $this->assertCount(0, $result);

        $result = $page->getTextXY(201, 720, 1, 1);
        $this->assertCount(1, $result);
        $this->assertCount(2, $result[0]);
        $this->assertEquals(
            [
                '0.999429',
                '0',
                '0',
                '1',
                '201.96',
                '720.68',
            ],
            $result[0][0]
        );
        $this->assertContains('Document title', $result[0][1]);

        // ------------------------------------------------------
        // Document is a form
        $filename = $this->rootDir.'/samples/SimpleInvoiceFilledExample1.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $result = $page->getTextXY(167, 894, 1, 1);
        $this->assertCount(1, $result);
        $this->assertCount(2, $result[0]);
        $this->assertEquals(
            [
                '1',
                '0',
                '0',
                '1',
                '167.3',
                '894.58',
            ],
            $result[0][0]
        );
        $this->assertContains('MyName  MyLastName', $result[0][1]);

        $result = $page->getTextXY(681, 877, 1, 1);
        $this->assertContains('1/1/2020', $result[0][1]);

        $result = $page->getTextXY(174, 827, 1, 1);
        $this->assertContains('Purchase 1', $result[0][1]);

        // ------------------------------------------------------
        // Document is another form of the same type
        $filename = $this->rootDir.'/samples/SimpleInvoiceFilledExample2.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $result = $page->getTextXY(167, 894, 1, 1);
        $this->assertEquals(
            [
                '1',
                '0',
                '0',
                '1',
                '167.3',
                '894.58',
            ],
            $result[0][0]
        );
        $this->assertContains("Other'sName  Other'sLastName", $result[0][1]);

        $result = $page->getTextXY(681, 877, 1, 1);
        $this->assertContains('2/2/2020', $result[0][1]);

        $result = $page->getTextXY(174, 827, 1, 1);
        $this->assertContains('Purchase 2', $result[0][1]);
    }
}

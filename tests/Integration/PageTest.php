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

use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element\ElementMissing;
use Smalot\PdfParser\Font;
use Smalot\PdfParser\Page;
use Tests\Smalot\PdfParser\TestCase;

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

    public function testGetFontsElementMissing()
    {
        $headerResources = $this->getMockBuilder('Smalot\PdfParser\Header')
            ->disableOriginalConstructor()
            ->getMock();

        $headerResources->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $headerResources->expects($this->once())
            ->method('get')
            ->willReturn(new ElementMissing());

        $header = $this->getMockBuilder('Smalot\PdfParser\Header')
            ->disableOriginalConstructor()
            ->getMock();

        $header->expects($this->once())
            ->method('get')
            ->willReturn($headerResources);

        $page = new Page(new Document(), $header);
        $fonts = $page->getFonts();

        $this->assertEmpty($fonts);
        $this->assertEquals([], $fonts);
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
        $this->assertStringContainsString('Document title', $text);
        $this->assertStringContainsString('Lorem ipsum', $text);

        $this->assertStringContainsString('Calibri', $text);
        $this->assertStringContainsString('Arial', $text);
        $this->assertStringContainsString('Times', $text);
        $this->assertStringContainsString('Courier New', $text);
        $this->assertStringContainsString('Verdana', $text);
    }

    /**
     * @see https://github.com/smalot/pdfparser/pull/457
     */
    public function testGetTextPullRequest457()
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/bugs/PullRequest457.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $text = $page->getText();

        $this->assertTrue(1000 < \strlen($text));
        $this->assertStringContainsString('SUPER', $text);
        $this->assertStringContainsString('VOORDEEL', $text);
        $this->assertStringContainsString('KRANT', $text);
        $this->assertStringContainsString('DINSDAG', $text);
        $this->assertStringContainsString('Snelfilterkoffie', $text);
        $this->assertStringContainsString('AardappelenZak', $text);
        $this->assertStringContainsString('ALL', $text);
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

        $btItem = $extractedRawData[0];
        $this->assertCount(3, $btItem);
        $this->assertArrayHasKey('t', $btItem);
        $this->assertArrayHasKey('o', $btItem);
        $this->assertArrayHasKey('c', $btItem);

        $this->assertEquals('BT', $btItem['o']);

        $tmItem = $extractedRawData[2];

        $this->assertcount(174, $extractedRawData);
        $this->assertCount(3, $tmItem);

        $this->assertArrayHasKey('t', $tmItem);
        $this->assertArrayHasKey('o', $tmItem);
        $this->assertArrayHasKey('c', $tmItem);

        $this->assertStringContainsString('Tm', $tmItem['o']);
        $this->assertStringContainsString('0.999429 0 0 1 201.96 720.68', $tmItem['c']);
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
        $tmItem = $extractedDecodedRawData[2];
        $this->assertCount(174, $extractedDecodedRawData);
        $this->assertCount(3, $tmItem);

        $this->assertArrayHasKey('t', $tmItem);
        $this->assertArrayHasKey('o', $tmItem);
        $this->assertArrayHasKey('c', $tmItem);

        $this->assertStringContainsString('Tm', $tmItem['o']);
        $this->assertStringContainsString('0.999429 0 0 1 201.96 720.68', $tmItem['c']);

        $this->assertCount(3, $tmItem);
        $this->assertArrayHasKey('t', $tmItem);
        $this->assertArrayHasKey('o', $tmItem);
        $this->assertArrayHasKey('c', $tmItem);

        $tjItem = $extractedDecodedRawData[3];
        $this->assertStringContainsString('TJ', $tjItem['o']);
        $this->assertStringContainsString('(', $tjItem['c'][0]['t']);
        $this->assertStringContainsString('D', $tjItem['c'][0]['c']);
        $this->assertStringContainsString('n', $tjItem['c'][1]['t']);
        $this->assertStringContainsString('0.325008', $tjItem['c'][1]['c']);
        $this->assertStringContainsString('(', $tjItem['c'][2]['t']);
        $this->assertStringContainsString('o', $tjItem['c'][2]['c']);
    }

    public function testExtractRawDataWithCorruptedPdf()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to find xref (PDF corrupted?)');

        $this
            ->getParserInstance()
            ->parseFile($this->rootDir.'/samples/corrupted.pdf')
            ->getPages();
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
        $this->assertCount(168, $dataCommands);

        $tmItem = $dataCommands[1];
        $this->assertCount(3, $tmItem);
        $this->assertArrayHasKey('t', $tmItem);
        $this->assertArrayHasKey('o', $tmItem);
        $this->assertArrayHasKey('c', $tmItem);

        $this->assertStringContainsString('Tm', $tmItem['o']);
        $this->assertStringContainsString('0.999429 0 0 1 201.96 720.68', $tmItem['c']);

        $tjItem = $dataCommands[2];
        $this->assertCount(3, $tjItem);
        $this->assertArrayHasKey('t', $tjItem);
        $this->assertArrayHasKey('o', $tjItem);
        $this->assertArrayHasKey('c', $tjItem);

        $this->assertStringContainsString('TJ', $tjItem['o']);
        $this->assertStringContainsString('(', $tjItem['c'][0]['t']);
        $this->assertStringContainsString('D', $tjItem['c'][0]['c']);
        $this->assertStringContainsString('n', $tjItem['c'][1]['t']);
        $this->assertStringContainsString('0.325008', $tjItem['c'][1]['c']);
        $this->assertStringContainsString('(', $tjItem['c'][2]['t']);
        $this->assertStringContainsString('o', $tjItem['c'][2]['c']);
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

        $this->assertStringContainsString('Document title', $item[1]);
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

        $this->assertStringContainsString('Calibri : Lorem ipsum dolor sit amet, consectetur a', $item[1]);

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
        $this->assertStringContainsString('nenatis.', $item[1]);

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
        $this->assertStringContainsString('MyName  MyLastName', $item[1]);

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
        $this->assertStringContainsString('1/1/2020', $item[1]);

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
        $this->assertStringContainsString('Purchase 1', $item[1]);

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
        $this->assertStringContainsString("Other'sName  Other'sLastName", $item[1]);

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
        $this->assertStringContainsString('2/2/2020', $item[1]);

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
        $this->assertStringContainsString('Purchase 2', $item[1]);
    }

    /**
     * Tests getDataTm with hexadecimal encoded document text.
     *
     * @see https://github.com/smalot/pdfparser/issues/336
     */
    public function testGetDataTmIssue336()
    {
        $filename = $this->rootDir.'/samples/bugs/Issue336_decode_hexadecimal.pdf';
        $document = $this->getParserInstance()->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();

        $item = $dataTm[2];
        $this->assertCount(13, $dataTm);
        $this->assertCount(2, $item);
        $this->assertCount(6, $item[0]);
        $this->assertEquals(
            [
                '1',
                '0',
                '0',
                '1',
                '318.185',
                '665.044',
            ],
            $item[0]
        );
        $this->assertEquals('Lorem', $item[1]);
    }

    /**
     * Tests that getPages() only returns Page objects
     *
     * @see https://github.com/smalot/pdfparser/issues/331
     *
     * Sample pdf file provided by @Reqrefusion, see
     * https://github.com/smalot/pdfparser/pull/350#issuecomment-703195220
     */
    public function testGetPages()
    {
        $filename = $this->rootDir.'/samples/bugs/Issue331.pdf';
        $document = $this->getParserInstance()->parseFile($filename);
        $pages = $document->getPages();

        // This should actually be 3 pages, but as long as the cause for issue #331
        // has not been found and the issue is not fixed, we'll settle for 2 here.
        // We still test for the count, so in case the bug should be fixed
        // unknowingly, we don't forget to resolve the issue as well and make sure
        // this assertion is present.
        $this->assertCount(2, $pages);

        foreach ($pages as $page) {
            $this->assertTrue($page instanceof Page);
        }
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
        $this->assertStringContainsString('Document title', $result[0][1]);

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
        $this->assertStringContainsString('Document title', $result[0][1]);

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
        $this->assertStringContainsString('MyName  MyLastName', $result[0][1]);

        $result = $page->getTextXY(681, 877, 1, 1);
        $this->assertStringContainsString('1/1/2020', $result[0][1]);

        $result = $page->getTextXY(174, 827, 1, 1);
        $this->assertStringContainsString('Purchase 1', $result[0][1]);

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
        $this->assertStringContainsString("Other'sName  Other'sLastName", $result[0][1]);

        $result = $page->getTextXY(681, 877, 1, 1);
        $this->assertStringContainsString('2/2/2020', $result[0][1]);

        $result = $page->getTextXY(174, 827, 1, 1);
        $this->assertStringContainsString('Purchase 2', $result[0][1]);
    }

    public function testExtractDecodedRawDataIssue450()
    {
        $filename = $this->rootDir.'/samples/bugs/Issue450.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $extractedDecodedRawData = $page->extractDecodedRawData();
        $this->assertIsArray($extractedDecodedRawData);
        $this->assertGreaterThan(3, \count($extractedDecodedRawData));
        $this->assertIsArray($extractedDecodedRawData[3]);
        $this->assertEquals('TJ', $extractedDecodedRawData[3]['o']);
        $this->assertIsArray($extractedDecodedRawData[3]['c']);
        $this->assertIsArray($extractedDecodedRawData[3]['c'][0]);
        $this->assertEquals(3, \count($extractedDecodedRawData[3]['c'][0]));
        $this->assertEquals('{signature:signer505906:Please+Sign+Here}', $extractedDecodedRawData[3]['c'][0]['c']);
    }

    public function testGetDataTmIssue450()
    {
        $filename = $this->rootDir.'/samples/bugs/Issue450.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();
        $this->assertIsArray($dataTm);
        $this->assertEquals(1, \count($dataTm));
        $this->assertIsArray($dataTm[0]);
        $this->assertEquals(2, \count($dataTm[0]));
        $this->assertIsArray($dataTm[0][0]);
        $this->assertEquals(6, \count($dataTm[0][0]));
        $this->assertEquals(1, $dataTm[0][0][0]);
        $this->assertEquals(0, $dataTm[0][0][1]);
        $this->assertEquals(0, $dataTm[0][0][2]);
        $this->assertEquals(1, $dataTm[0][0][3]);
        $this->assertEquals(67.5, $dataTm[0][0][4]);
        $this->assertEquals(756.25, $dataTm[0][0][5]);
        $this->assertEquals('{signature:signer505906:Please+Sign+Here}', $dataTm[0][1]);
    }
}

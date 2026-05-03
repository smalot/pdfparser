<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2020-06-01
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

namespace PHPUnitTests\Integration;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element\ElementMissing;
use Smalot\PdfParser\Font;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\Parser;

class PageTest extends TestCase
{
    /**
     * @group pdfjs-dataset-local
     *
     * @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/boundingBox_invalid.pdf
     */
    public function testInvalidBoundingBoxesFallbackLikePdfJs(): void
    {
        $fixture = $this->rootDir.'/samples/bugs/rawdata/boundingBox_invalid.pdf';
        self::assertFileExists($fixture, 'Missing fixture: '.$fixture);

        $document = (new Parser())->parseFile($fixture);
        $pages = $document->getPages();

        self::assertCount(3, $pages);

        // Page 1 has empty MediaBox => fallback to Letter size.
        self::assertSame([612.0, 792.0], $this->extractBoxSize($pages[0], 'MediaBox'));

        // Page 2 has empty CropBox => fallback to MediaBox.
        self::assertSame([800.0, 600.0], $this->extractBoxSize($pages[1], 'CropBox'));
        self::assertSame([800.0, 600.0], $this->extractBoxSize($pages[1], 'MediaBox'));

        // Page 3 keeps explicit MediaBox and CropBox values.
        self::assertSame([600.0, 800.0], $this->extractBoxSize($pages[2], 'MediaBox'));
        self::assertSame([400.0, 200.0], $this->extractBoxSize($pages[2], 'CropBox'));

        self::assertSame(
            [
                ['width' => 612.0, 'height' => 792.0],
                ['width' => 800.0, 'height' => 600.0],
                ['width' => 400.0, 'height' => 200.0],
            ],
            $document->getPagesDimensions()
        );

        self::assertSame(
            [
                ['width' => 612.0, 'height' => 792.0],
                ['width' => 800.0, 'height' => 600.0],
                ['width' => 600.0, 'height' => 800.0],
            ],
            $document->getPagesDimensions('MediaBox')
        );

        self::assertSame(
            ['width' => 612.0, 'height' => 792.0],
            $pages[0]->getDimensions()
        );

        self::assertSame(
            ['width' => 612.0, 'height' => 792.0],
            $pages[0]->getDimensions('MediaBox')
        );

        self::assertNull($pages[0]->getDimensions('BleedBox'));
    }

    public function testInvertedMediaBoxCoordinatesAreNormalized(): void
    {
        $document = new Document();
        $header = Header::parse('<</Type/Page/MediaBox [595 842 0 0]>>', $document);
        $page = new Page($document, $header, null);

        self::assertSame(
            ['width' => 595.0, 'height' => 842.0],
            $page->getDimensions('MediaBox')
        );

        self::assertSame([595.0, 842.0], $this->extractBoxSize($page, 'MediaBox'));
    }

    /**
     * @group pdfjs-dataset-local
     *
     * @dataProvider providePdfJsFixtureRegressionByProvenance
     *
     * @param array<int, array{0: float|null, 1: float|null}> $expectedPageDimensions
     */
    public function testPdfJsFixturePageCountAndDimensionsByProvenance(
        string $fixturePath,
        array $expectedPageDimensions
    ): void {
        $this->assertPdfJsFixturePageCountAndDimensionsByProvenance(
            $fixturePath,
            $expectedPageDimensions
        );
    }

    /**
     * @group pdfjs-corrupted
     *
     * @dataProvider provideCorruptedPdfJsFixtureRegressionByProvenance
     *
     * @param array<int, array{0: float|null, 1: float|null}> $expectedPageDimensions
     */
    public function testCorruptedPdfJsFixturePageCountAndDimensionsByProvenance(
        string $fixturePath,
        array $expectedPageDimensions
    ): void {
        $this->assertPdfJsFixturePageCountAndDimensionsByProvenance(
            $fixturePath,
            $expectedPageDimensions
        );
    }

    /**
     * @param array<int, array{0: float|null, 1: float|null}> $expectedPageDimensions
     */
    private function assertPdfJsFixturePageCountAndDimensionsByProvenance(
        string $fixturePath,
        array $expectedPageDimensions
    ): void {
        $absolutePath = $this->rootDir.'/samples/bugs/rawdata/'.$fixturePath;
        self::assertFileExists($absolutePath, 'Missing fixture: '.$absolutePath);

        $document = (new Parser())->parseFile($absolutePath);

        $this->assertDocumentPageCountAndDimensions($document, $expectedPageDimensions);
    }

    /**
     * @return iterable<string, array{string, array<int, array{0: float|null, 1: float|null}>}>
     */
    public static function providePdfJsFixtureRegressionByProvenance(): iterable
    {
        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/Pages-tree-refs.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/Pages-tree-refs.pdf
        yield 'Pages-tree-refs' => ['Pages-tree-refs.pdf', [[595.0, 842.0], [595.0, 842.0]]];

        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/boundingBox_invalid.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/boundingBox_invalid.pdf
        yield 'boundingBox_invalid' => ['boundingBox_invalid.pdf', [[612.0, 792.0], [800.0, 600.0], [400.0, 200.0]]];

        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/copy_paste_ligatures.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/copy_paste_ligatures.pdf
        yield 'copy_paste_ligatures' => ['copy_paste_ligatures.pdf', [[142.7429, 14.218]]];

        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/issue16091.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/issue16091.pdf
        yield 'issue16091' => ['issue16091.pdf', [[88.7177, 33.676]]];

        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/issue19484_1.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/issue19484_1.pdf
        // Valid PDF with an unusual declared encryption scheme; pdf.js opens it without
        // prompting for a user password and we should still expose the page geometry.
        yield 'issue19484_1' => ['issue19484_1.pdf', [[612.0, 792.0]]];

        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/issue19484_2.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/issue19484_2.pdf
        // Valid PDF with an unusual declared encryption scheme; pdf.js opens it without
        // prompting for a user password and we should still expose the page geometry.
        yield 'issue19484_2' => ['issue19484_2.pdf', [[612.0, 792.0]]];

        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/issue7872.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/issue7872.pdf
        yield 'issue7872' => ['issue7872.pdf', [[250.0, 50.0]]];

    }

    /**
     * @return iterable<string, array{string, array<int, array{0: float|null, 1: float|null}>}>
     */
    public static function provideCorruptedPdfJsFixtureRegressionByProvenance(): iterable
    {
        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/poppler-742-0-fuzzed.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/poppler-742-0-fuzzed.pdf
        // pdf.js cannot load this fuzzed file reliably; we keep it isolated from
        // the regular regression set.
        yield 'poppler-742-0-fuzzed' => ['poppler-742-0-fuzzed.pdf', [[595.276, 841.89]]];
    }

    public function testGetFonts(): void
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

    /**
     * @return array{0: float, 1: float}
     */
    private function extractBoxSize(Page $page, string $boxName): array
    {
        $box = $page->get($boxName);
        self::assertTrue(is_object($box) && method_exists($box, 'getContent'));

        $content = $box->getContent();
        self::assertIsArray($content);
        self::assertGreaterThanOrEqual(4, count($content));

        $coordinates = [];
        foreach (array_slice($content, 0, 4) as $value) {
            if (is_object($value) && method_exists($value, 'getContent')) {
                $value = $value->getContent();
            }
            self::assertIsNumeric($value);
            $coordinates[] = (float) $value;
        }

        return [
            $coordinates[2] - $coordinates[0],
            $coordinates[3] - $coordinates[1],
        ];
    }

    public function testGetFontsElementMissing(): void
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

    public function testGetFont(): void
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

    public function testGetText(): void
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
     * @group memory-heavy
     * @group linux-only
     *
     * @see https://github.com/smalot/pdfparser/pull/457
     */
    public function testGetTextPullRequest457(): void
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/bugs/PullRequest457.pdf';
        $config = new Config();
        $config->setRetainImageContent(false);
        $parser = $this->getParserInstance($config);
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
        $this->assertStringContainsString('Aardappelen'."\n".'Zak', $text);
        $this->assertStringContainsString('ALL', $text);
    }

    public function testExtractRawData(): void
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $extractedRawData = $page->extractRawData();

        $btItem = $extractedRawData[4];
        $this->assertCount(3, $btItem);
        $this->assertArrayHasKey('t', $btItem);
        $this->assertArrayHasKey('o', $btItem);
        $this->assertArrayHasKey('c', $btItem);

        $this->assertEquals('BT', $btItem['o']);

        $tmItem = $extractedRawData[6];

        $this->assertcount(185, $extractedRawData);
        $this->assertCount(3, $tmItem);

        $this->assertArrayHasKey('t', $tmItem);
        $this->assertArrayHasKey('o', $tmItem);
        $this->assertArrayHasKey('c', $tmItem);

        $this->assertStringContainsString('Tm', $tmItem['o']);
        $this->assertStringContainsString('0.999429 0 0 1 201.96 720.68', $tmItem['c']);
    }

    public function testExtractDecodedRawData(): void
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $extractedDecodedRawData = $page->extractDecodedRawData();
        $tmItem = $extractedDecodedRawData[6];
        $this->assertCount(185, $extractedDecodedRawData);
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

        $tjItem = $extractedDecodedRawData[7];
        $this->assertStringContainsString('TJ', $tjItem['o']);
        $this->assertStringContainsString('(', $tjItem['c'][0]['t']);
        $this->assertStringContainsString('D', $tjItem['c'][0]['c']);
        $this->assertStringContainsString('n', $tjItem['c'][1]['t']);
        $this->assertStringContainsString('0.325008', $tjItem['c'][1]['c']);
        $this->assertStringContainsString('(', $tjItem['c'][2]['t']);
        $this->assertStringContainsString('o', $tjItem['c'][2]['c']);
    }

    public function testExtractRawDataWithCorruptedPdf(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to find xref (PDF corrupted?)');

        $this
            ->getParserInstance()
            ->parseFile($this->rootDir.'/samples/corrupted.pdf')
            ->getPages();
    }

    public function testGetDataCommands(): void
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataCommands = $page->getDataCommands();
        $this->assertCount(185, $dataCommands);

        $tmItem = $dataCommands[6];
        $this->assertCount(3, $tmItem);
        $this->assertArrayHasKey('t', $tmItem);
        $this->assertArrayHasKey('o', $tmItem);
        $this->assertArrayHasKey('c', $tmItem);

        $this->assertStringContainsString('Tm', $tmItem['o']);
        $this->assertStringContainsString('0.999429 0 0 1 201.96 720.68', $tmItem['c']);

        $tjItem = $dataCommands[7];
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

    public function testGetDataTm(): void
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
            [
                round($item[0][0], 6),
                round($item[0][1], 6),
                round($item[0][2], 6),
                round($item[0][3], 6),
                round($item[0][4], 2),
                round($item[0][5], 2),
            ]
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
            [
                round($item[0][0], 6),
                round($item[0][1], 6),
                round($item[0][2], 6),
                round($item[0][3], 6),
                round($item[0][4], 2),
                round($item[0][5], 2),
            ]
        );
        $this->assertStringContainsString('Calibri : Lorem ipsum dolor sit amet, consectetur a', $item[1]);

        $item = $dataTm[80];
        $this->assertEquals(
            [
                '0.999402',
                '0',
                '0',
                '1',
                '342.84',
                '81.44',
            ],
            [
                round($item[0][0], 6),
                round($item[0][1], 6),
                round($item[0][2], 6),
                round($item[0][3], 6),
                round($item[0][4], 2),
                round($item[0][5], 2),
            ]
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

        // test if scaling by fontSize (Tf, Tfs) and test matrix (Tm) are taken into account
        $dataCommands = [
            ['t' => '', 'o' => 'BT', 'c' => ''], // begin text
            ['t' => '/', 'o' => 'Tf', 'c' => 'TT0 1'], // set font and scale font by 1 pt
            ['t' => '', 'o' => 'Tm', 'c' => '7.5 -0 0 8.5 45.36 791.52'], // additionally scale by 7.5 pt
            ['t' => '', 'o' => 'Td', 'c' => '0.568 0'], // move 0.568 * 7.5 pts (7.5 is horizontal scaling) to the right
            ['t' => '(', 'o' => 'Tj', 'c' => 'test'], // print "test"
            ['t' => '', 'o' => 'TD', 'c' => '-3.5 -1.291'], // move 3.5 * 7.5 pts left, 1.291 * 8.5 (vertical scaling) pts down and set text leading to 9.464
            ['t' => '(', 'o' => 'Tj', 'c' => 'another test'], // print "another test"
            ['t' => '', 'o' => '\'', 'c' => 'again a test'], // go to next line and print "again a test"
            ['t' => '', 'o' => 'TL', 'c' => '5'], // set text leading by TL
            ['t' => '', 'o' => '\'', 'c' => 'the next line'], // go to next line and print "the next line"
        ];

        // verify scaling is taken into account for Td
        $dataTm = $page->getDataTm($dataCommands);
        $item = $dataTm[0];
        $this->assertEquals(
            [
                '7.5',
                '-0',
                '0',
                '8.5',
                '49.62',
                '791.52',
            ],
            $item[0]
        );

        // verify scaling is taken into account for TD
        $item = $dataTm[1];
        $this->assertEquals(
            [
                '7.5',
                '-0',
                '0',
                '8.5',
                '23.37',
                '780.5465',
            ],
            $item[0]
        );

        // verify scaling is taken into account for text leading set by TD
        $item = $dataTm[2];
        $this->assertEquals(
            [
                '7.5',
                '-0',
                '0',
                '8.5',
                '23.37',
                '769.573',
            ],
            $item[0]
        );

        // verify scaling is taken into account for text leading set by TL
        $item = $dataTm[3];
        $this->assertEquals(
            [
                '7.5',
                '-0',
                '0',
                '8.5',
                '23.37',
                '727.073',
            ],
            $item[0]
        );
    }

    public function testDataTmFontInfoHasToBeIncluded(): void
    {
        $config = new Config();
        $config->setDataTmFontInfoHasToBeIncluded(true);

        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance($config);
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();
        $fonts = $page->getFonts();

        $item = $dataTm[0];
        $this->assertCount(4, $item);
        $this->assertEquals($item[2], 'R7');
        $this->assertEquals($item[3], '27.96');
        $this->assertArrayHasKey('R7', $fonts);
        $item = $dataTm[80];
        $this->assertCount(4, $item);
        $this->assertEquals($item[2], 'R14');
        $this->assertEquals($item[3], '11.04');
        $this->assertArrayHasKey('R7', $fonts);

        $filename = $this->rootDir.'/samples/InternationalChars.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();
        $fonts = $page->getFonts();

        $item = $dataTm[88];
        $this->assertEquals($item[2], 'C2_0');
        $this->assertEquals($item[3], '1');
        $this->assertArrayHasKey('C2_0', $fonts);
        foreach ($dataTm as $item) {
            $this->assertCount(4, $item);
        }
    }

    /**
     * Tests getDataTm with hexadecimal encoded document text.
     *
     * @see https://github.com/smalot/pdfparser/issues/336
     */
    public function testGetDataTmIssue336(): void
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
    public function testGetPages(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue331.pdf';
        $document = $this->getParserInstance()->parseFile($filename);
        $pages = $document->getPages();

        /*
         * The problem of issue #331 is fixed by the pull request of the issue #479.
         * The original Issue331.pdf was modified so for the updated version (actual
         * version) a new xref was added and now the valid /Index has the following value:
         *    [1 1 3 1 7 1 175 1 178 1 219 2]
         * This means, that there a 6 pairs containing the values for 'first object id'
         * and 'number of objects'. Till now only the first entry was used and so the
         * objects of all following entries gots a wrong id.
         * By the fix of issue #479 now the expected number of pages is counted.
         */
        $this->assertCount(3, $pages);

        foreach ($pages as $page) {
            $this->assertTrue($page instanceof Page);
        }
    }

    public function testGetTextXY(): void
    {
        // Document with text.
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $result = $page->getTextXY(201.96, 720.68, 0.01, 0.01);
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
            [
                round($result[0][0][0], 6),
                round($result[0][0][1], 6),
                round($result[0][0][2], 6),
                round($result[0][0][3], 6),
                round($result[0][0][4], 2),
                round($result[0][0][5], 2),
            ]
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
            [
                round($result[0][0][0], 6),
                round($result[0][0][1], 6),
                round($result[0][0][2], 6),
                round($result[0][0][3], 6),
                round($result[0][0][4], 2),
                round($result[0][0][5], 2),
            ]
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

    public function testExtractDecodedRawDataIssue450(): void
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

    public function testGetDataTmIssue450(): void
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

    public function testIsFpdf(): void
    {
        $filename = $this->rootDir.'/samples/Document1_foxitreader.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $this->assertFalse($page->isFpdf());
        $filename = $this->rootDir.'/samples/bugs/Issue454.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $this->assertTrue($page->isFpdf());
    }

    public function testGetPageNumber(): void
    {
        $filename = $this->rootDir.'/samples/Document1_foxitreader.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $this->assertEquals(0, $page->getPageNumber());
        $filename = $this->rootDir.'/samples/Document1_pdfcreator.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $this->assertEquals(0, $page->getPageNumber());
        $filename = $this->rootDir.'/samples/Document2_pdfcreator_nocompressed.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $this->assertEquals(0, $page->getPageNumber());
        $filename = $this->rootDir.'/samples/InternationalChars.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $this->assertEquals(0, $page->getPageNumber());
        $filename = $this->rootDir.'/samples/SimpleInvoiceFilledExample1.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $this->assertEquals(0, $page->getPageNumber());
        $filename = $this->rootDir.'/samples/bugs/Issue454.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $this->assertEquals(0, $page->getPageNumber());
        $page = $pages[1];
        $this->assertEquals(1, $page->getPageNumber());
        $page = $pages[2];
        $this->assertEquals(2, $page->getPageNumber());
        $page = $pages[3];
        $this->assertEquals(3, $page->getPageNumber());
    }

    public function testIssue454(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue454.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();
        $this->assertIsArray($dataTm);
        $this->assertGreaterThan(0, \count($dataTm));
        $this->assertIsArray($dataTm[0]);
        $this->assertEquals(2, \count($dataTm[0]));
        $this->assertIsArray($dataTm[0][0]);
        $this->assertEquals(6, \count($dataTm[0][0]));
        $this->assertEquals(201.96, round($dataTm[0][0][4], 2));
        $this->assertEquals(720.68, round($dataTm[0][0][5], 2));
        $this->assertStringContainsString('Document title', $dataTm[0][1]);
        $textData = $page->getTextXY(201.96, 720.68, 0.01, 0.01);
        $this->assertStringContainsString('Document title', $textData[0][1]);
        $page = $pages[2];
        $dataTm = $page->getDataTm();
        $this->assertIsArray($dataTm);
        $this->assertGreaterThan(0, \count($dataTm));
        $this->assertIsArray($dataTm[0]);
        $this->assertEquals(2, \count($dataTm[0]));
        $this->assertIsArray($dataTm[0][0]);
        $this->assertEquals(6, \count($dataTm[0][0]));
        $this->assertEquals(67.5, $dataTm[0][0][4]);
        $this->assertEquals(756.25, $dataTm[0][0][5]);
        $this->assertStringContainsString('{signature:signer505906:Please+Sign+Here}', $dataTm[0][1]);
        $textData = $page->getTextXY(67.5, 756.25);
        $this->assertStringContainsString('{signature:signer505906:Please+Sign+Here}', $textData[0][1]);
    }

    /**
     * Check that BT and ET do not reset the font.
     *
     * Data TM font info is included.
     *
     * @see https://github.com/smalot/pdfparser/pull/630
     */
    public function testIssue629WithDataTmFontInfo(): void
    {
        $config = new Config();
        $config->setDataTmFontInfoHasToBeIncluded(true);

        $filename = $this->rootDir.'/samples/bugs/Issue629.pdf';
        $parser = $this->getParserInstance($config);
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = end($pages);
        $dataTm = $page->getDataTm();

        $this->assertCount(4, $dataTm[0]);
        $this->assertEquals('F2', $dataTm[0][2]);
    }

    /**
     * Data TM font info is NOT included.
     *
     * @see https://github.com/smalot/pdfparser/pull/630
     */
    public function testIssue629WithoutDataTmFontInfo(): void
    {
        $config = new Config();

        $filename = $this->rootDir.'/samples/bugs/Issue629.pdf';
        $parser = $this->getParserInstance($config);
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = end($pages);
        $dataTm = $page->getDataTm();

        $this->assertCount(2, $dataTm[0]);
        $this->assertFalse(isset($dataTm[0][2]));
    }

    public function testCmCommandInPdfs(): void
    {
        $config = new Config();
        $parser = $this->getParserInstance($config);
        $filename = $this->rootDir.'/samples/Document-Word-Landscape-printedaspdf.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();
        $item = $dataTm[2];
        $this->assertCount(6, $dataTm);
        $this->assertCount(2, $item);
        $this->assertCount(6, $item[0]);
        $this->assertEquals('This is just a test', trim($item[1]));
        $this->assertEquals(
            [
                '0.75',
                '0.0',
                '0.0',
                '0.75',
                '59.16',
                '500.4',
            ],
            [
                round($item[0][0], 6),
                round($item[0][1], 6),
                round($item[0][2], 6),
                round($item[0][3], 6),
                round($item[0][4], 2),
                round($item[0][5], 2),
            ]
        );
    }

}

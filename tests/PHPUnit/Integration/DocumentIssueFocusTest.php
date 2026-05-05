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
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;

/**
 * Document related tests which are related to certain issues.
 */
class DocumentIssueFocusTest extends TestCase
{
    /**
     * Tests getText method without a given page limit.
     *
     * @see https://github.com/smalot/pdfparser/pull/562
     */
    public function testGetTextNoPageLimit(): void
    {
        $document = (new Parser())->parseFile($this->rootDir.'/samples/bugs/Issue331.pdf');

        self::assertStringContainsString('Medeni Usul ve İcra İflas Hukuku', $document->getText());
    }

    /**
     * Tests getText method with a given page limit.
     *
     * @see https://github.com/smalot/pdfparser/pull/562
     */
    public function testGetTextWithPageLimit(): void
    {
        $document = (new Parser())->parseFile($this->rootDir.'/samples/bugs/Issue331.pdf');

        // given text is on page 2, it has to be ignored because of that
        self::assertStringNotContainsString('Medeni Usul ve İcra İflas Hukuku', $document->getText(1));
    }

    /**
     * Tests extraction of XMP Metadata vs. getHeader() data.
     *
     * @see https://github.com/smalot/pdfparser/pull/606
     */
    public function testExtractXMPMetadata(): void
    {
        $document = (new Parser())->parseFile($this->rootDir.'/samples/XMP_Metadata.pdf');

        $details = $document->getDetails();

        // Test that the dc:title data was extracted from the XMP
        // Metadata.
        self::assertStringContainsString("Enhance PdfParser\u{2019}s Metadata Capabilities", $details['dc:title']);
    }

    /**
     * Tests PDFDocEncoding decode of Document Properties
     *
     * @see https://github.com/smalot/pdfparser/issues/609
     */
    public function testPDFDocEncodingDecode(): void
    {
        $document = (new Parser())->parseFile($this->rootDir.'/samples/bugs/Issue609.pdf');

        $details = $document->getDetails();

        // These test that Adobe-inserted \r are removed from a UTF-8
        // escaped metadata string, and the surrounding characters are
        // repaired
        $testKeywords = '˘ˇˆ˙˝˛˞˜•†‡…—–ƒ⁄‹›−‰„“”‘’‚™ﬁﬂŁŒŠŸŽıłœšž€¡¢£¤¥¦§¨©ª«¬®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿ';
        self::assertStringContainsString($testKeywords, $details['Keywords']);

        $testKeywords = 'added line-feeds often destroy multibyte characters';
        self::assertStringContainsString($testKeywords, $details['Keywords']);

        // This tests that the PDFDocEncoding characters that differ
        // from CP-1252 are decoded to their correct UTF-8 code points
        // as well as removing \r line-feeds
        $testSubject = '•†‡…—–ƒ⁄‹›−‰„“”‘’‚™ŁŒŠŸŽıłœšž';
        self::assertStringContainsString($testSubject, $details['Subject']);
    }
    /**
     * Data provider for pdf.js regression tests covering readable encrypted and large stream PDFs.
     *
     * @return iterable<string, array{string, array<int, array{0: float|null, 1: float|null}>}>
     */
    public static function pdfJsRegressionFixturesProvider(): iterable
    {
        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/bug900822.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/bug900822.pdf
        // RC4 Standard V1R2 encryption; readable without explicit user password.
        yield 'bug900822' => ['PullRequest809-pdf.js-bug900822.pdf', [[595.0, 841.89]]];

        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/issue17215.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/issue17215.pdf
        // RC4 Standard V2R3 encryption; readable without explicit user password.
        yield 'issue17215' => ['PullRequest810-pdf.js-issue17215.pdf', [[595.0, 842.0]]];

        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/issue19517.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/issue19517.pdf
        // Large stream decode resilience; parser must not exhaust memory.
        yield 'issue19517' => ['PullRequest811-pdf.js-issue19517.pdf', [[12608.0, 16806.0]]];

        // @see https://github.com/mozilla/pdf.js/blob/master/test/pdfs/PDFBOX-4352-0.pdf
        // @see https://raw.githubusercontent.com/mozilla/pdf.js/refs/heads/master/test/pdfs/PDFBOX-4352-0.pdf
        // Hybrid encrypted+malformed; page tree remains readable.
        yield 'PDFBOX-4352-0' => ['PullRequest812-pdf.js-PDFBOX-4352-0.pdf', [[200.0, 50.0]]];
    }

    /**
     * Tests parsing of pdf.js regression fixtures covering readable encrypted PDFs and large streams.
     *
     * Validates that:
     * - PDFs with encryption declarations can be parsed without explicit user password
     * - Parser handles large streams without memory exhaustion
     * - Hybrid encrypted+malformed PDFs maintain readable page trees
     * - Page dimensions (MediaBox) are correctly extracted
     *
     * @dataProvider pdfJsRegressionFixturesProvider
     * @group integration
     * @group rawdata-handling
     *
     * @param array<int, array{0: float|null, 1: float|null}> $expectedPageDimensions
     *
     * @see https://github.com/mozilla/pdf.js/tree/master/test/pdfs
     */
    public function testParseFileWithPdfJsRegressionFixtures(string $fixturePath, array $expectedPageDimensions): void
    {
        $absolutePath = $this->rootDir.'/samples/bugs/rawdata/'.$fixturePath;
        self::assertFileExists($absolutePath, 'Missing fixture: '.$absolutePath);

        $document = (new Parser())->parseFile($absolutePath);

        $this->assertDocumentPageCountAndDimensions($document, $expectedPageDimensions);
    }
}

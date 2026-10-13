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

namespace PHPUnitTests\Integration\RawData;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Exception\EmptyPdfException;
use Smalot\PdfParser\Exception\MissingPdfHeaderException;
use Smalot\PdfParser\RawData\RawDataParser;

class RawDataParserHelper extends RawDataParser
{
    /**
     * References of the objects which got decoded, in order of decoding.
     *
     * @var array<string>
     */
    public $decodedObjects = [];

    protected function getIndirectObject(string $pdfData, array $xref, string $objRef, int $offset = 0, bool $decoding = true): array
    {
        $this->decodedObjects[] = $objRef;

        return parent::getIndirectObject($pdfData, $xref, $objRef, $offset, $decoding);
    }

    /**
     * Expose protected function "getRawObject".
     */
    public function exposeGetRawObject($pdfData, $offset = 0)
    {
        return $this->getRawObject($pdfData, $offset);
    }

    /**
     * Expose protected function "getXrefData".
     */
    public function exposeGetXrefData(string $pdfData, int $offset = 0, array $xref = [], array $visitedOffsets = []): array
    {
        return $this->getXrefData($pdfData, $offset, $xref, $visitedOffsets);
    }

    /**
     * Expose protected function "decodeXref".
     */
    public function exposeDecodeXref(string $pdfData, int $startxref, array $xref = [], array $visitedOffsets = []): array
    {
        return $this->decodeXref($pdfData, $startxref, $xref, $visitedOffsets);
    }

    /**
     * Expose protected function "decodeXrefStream".
     */
    public function exposeDecodeXrefStream(string $pdfData, int $startxref, array $xref = [], array $visitedOffsets = []): array
    {
        return $this->decodeXrefStream($pdfData, $startxref, $xref, $visitedOffsets);
    }
}

class RawDataParserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new RawDataParserHelper([], new Config());
    }

    /**
     * Tests buggy behavior of getRawObject.
     *
     * When PDF has corrupted xref table getRawObject may run into an infinite loop.
     *
     * @see https://github.com/smalot/pdfparser/issues/372
     * @see https://github.com/smalot/pdfparser/pull/377
     */
    public function testGetRawObjectIssue372(): void
    {
        // The following $data content is a minimal example to trigger the infinite loop
        $data = '<</Producer (eDkºãa˜þõ‚LÅòÕ�PïÙ��)©)>>';

        // calling "getRawObject" via "exposeGetRawObject" would result in an infinite loop
        // if the fix is not there.
        $result = $this->fixture->exposeGetRawObject($data);

        $this->assertEquals(
            [
                '<<',
                [
                    ['/', 'Producer', 11],
                    ['(', 'eDkºãa˜þõ‚LÅòÕ�PïÙ��', 52],
                ],
                52,
            ],
            $result
        );

        // Test that spaces after a 'stream' declaration are absorbed
        // See: https://github.com/smalot/pdfparser/issues/641
        $data = 'stream '."\n";
        $data .= 'streamdata'."\n";
        $data .= 'endstream'."\n";
        $data .= 'endobj';

        $result = $this->fixture->exposeGetRawObject($data);

        // Value 'streamdata'."\n" would be empty string without the fix
        $this->assertEquals(
            [
                'stream',
                'streamdata'."\n",
                19,
            ],
            $result
        );
    }

    /**
     * Tests buggy behavior of decodeXrefStream.
     *
     * @see https://github.com/smalot/pdfparser/issues/30
     * @see https://github.com/smalot/pdfparser/issues/192
     * @see https://github.com/smalot/pdfparser/issues/209
     * @see https://github.com/smalot/pdfparser/issues/330
     * @see https://github.com/smalot/pdfparser/issues/356
     * @see https://github.com/smalot/pdfparser/issues/373
     * @see https://github.com/smalot/pdfparser/issues/392
     * @see https://github.com/smalot/pdfparser/issues/397
     */
    public function testDecodeXrefStreamIssue356(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue356.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();

        $this->assertStringContainsString('Ημερήσια έκθεση επιδημιολογικής', $pages[0]->getText());
    }

    public function testDecodeObjectHeaderIssue405(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue405.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();

        $this->assertStringContainsString('Bug fix: PR #405', $pages[0]->getText());
    }

    /**
     * Tests buggy behavior of decodeXrefStream.
     *
     * When PDF has more than one entry in the /Index area (for example by changing
     * the document description), only the first entry is used.
     * If the fix is not used the array returned by getDetails() contains only the entry
     * with the key 'Pages'. All other entries like 'Author', 'Creator', 'Title',
     * 'Subject' (which come from the 'Info' object) are not listed, because the
     * 'Info' object gets a wrong object id during parsing the data into the xref structure.
     * So the object id listed at the /Info entry is not valid and the data of the info object
     * cannot be loaded during executing Document::buildDetails().
     *
     * @see https://github.com/smalot/pdfparser/pull/479
     */
    public function testDecodeXrefStreamIssue479(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue479.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $details = $document->getDetails();

        $this->assertArrayHasKey('Author', $details);
        $this->assertArrayHasKey('CreationDate', $details);
        $this->assertArrayHasKey('Creator', $details);
        $this->assertArrayHasKey('ModDate', $details);
        $this->assertArrayHasKey('Producer', $details);
        $this->assertArrayHasKey('Subject', $details);
        $this->assertArrayHasKey('Title', $details);
    }

    /**
     * Account for inaccurate offset values in getXrefData.
     *
     * Normally offset values extracted from the PDF document are exact.
     * However in some cases, they may point to whitespace *before* a
     * valid xref keyword. Move the offset forward past whitespace to
     * make this function a little more lenient.
     *
     * @see https://github.com/smalot/pdfparser/issues/673
     */
    public function testGetXrefDataIssue673(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue673.pdf';

        // Parsing this document would previously throw an Exception
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $text = $document->getText();

        self::assertStringContainsString('6 rue des Goutais', $text);
    }

    /**
     * Handle self referencing xref
     *
     * It seems that some PDF creators output `Prev 0` when there is no previous xref.
     *
     * @see https://github.com/smalot/pdfparser/pull/727
     */
    public function testDecodeXrefIssue727(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue727.pdf';

        // Parsing this document would previously cause an infinite loop
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $text = $document->getText();

        self::assertStringContainsString('', $text);
    }

    /**
     * Test that getXrefData prevents circular references
     *
     * When a PDF has circular references in xref chain (e.g., Prev pointing to already visited offset),
     * the parser should detect this and stop recursion to prevent infinite loops.
     */
    public function testGetXrefDataPreventsCircularReferences(): void
    {
        // Create a minimal PDF structure with xref that would create a circular reference
        $pdfData = "%PDF-1.5\n";
        $pdfData .= "xref\n";
        $pdfData .= "0 1\n";
        $pdfData .= "0000000000 65535 f \n";
        $pdfData .= "trailer\n";
        $pdfData .= "<</Size 1/Prev 7>>\n";  // Prev points back to offset 7 (the xref keyword)
        $pdfData .= "startxref\n";
        $pdfData .= "7\n";
        $pdfData .= "%%EOF\n";

        // Test with visitedOffsets containing the offset we're trying to visit
        $result = $this->fixture->exposeGetXrefData($pdfData, 7, [], [7]);

        // Should return empty xref array without recursing
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that decodeXref passes visitedOffsets correctly when handling Prev
     *
     * This ensures that circular reference detection works when decodeXref
     * calls getXrefData for a Prev pointer.
     */
    public function testDecodeXrefPassesVisitedOffsets(): void
    {
        // Create a minimal xref structure with Prev
        $pdfData = "xref\n";
        $pdfData .= "0 1\n";
        $pdfData .= "0000000000 65535 f \n";
        $pdfData .= "trailer\n";
        $pdfData .= "<</Size 1/Prev 100>>\n";

        // Call decodeXref with visitedOffsets that includes the Prev offset
        // This should not cause infinite recursion
        $result = $this->fixture->exposeDecodeXref($pdfData, 0, [], [100]);

        // Should complete without error and return an array
        $this->assertIsArray($result);
        $this->assertArrayHasKey('trailer', $result);
    }

    /**
     * Test that getXrefData tracks visited offsets correctly
     *
     * Ensures that offsets are added to visitedOffsets array to prevent
     * circular references in subsequent calls.
     */
    public function testGetXrefDataTracksVisitedOffsets(): void
    {
        // Test that calling with an already-visited offset returns immediately
        $pdfData = "%PDF-1.5\n";
        $pdfData .= "xref\n";
        $pdfData .= "0 1\n";
        $pdfData .= "0000000000 65535 f \n";
        $pdfData .= "trailer\n";
        $pdfData .= "<</Size 1>>\n";
        $pdfData .= "startxref\n";
        $pdfData .= "7\n";
        $pdfData .= "%%EOF\n";

        // Call with offset 50 already in visitedOffsets - should return immediately
        $result = $this->fixture->exposeGetXrefData($pdfData, 50, [], [50]);

        // Should return empty array without processing
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Objects used by the tests below. Object 3 is left out, it becomes a free entry.
     *
     * @return array<int, string>
     */
    private function getObjects(): array
    {
        return [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [] /Count 0 >>',
            4 => '<< /Foo (bar) >>',
        ];
    }

    public function testParseHeaderAndXrefEmptyData(): void
    {
        $this->expectException(EmptyPdfException::class);

        $this->fixture->parseHeaderAndXref('');
    }

    /**
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=47 ISO 32000-1:2008, 7.5.2 (file header)
     */
    public function testParseHeaderAndXrefMissingHeader(): void
    {
        $this->expectException(MissingPdfHeaderException::class);

        $this->fixture->parseHeaderAndXref('1 0 obj << /Type /Catalog >> endobj');
    }

    /**
     * The cross-reference table provides the byte offset of each object in use,
     * keyed by "[object number]_[generation number]". Free entries are left out.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=48 ISO 32000-1:2008, 7.5.4 (cross-reference table)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=51 ISO 32000-1:2008, 7.5.5, Table 15 (entries in the file trailer dictionary)
     */
    public function testParseHeaderAndXref(): void
    {
        $pdf = $this->createPdf($this->getObjects());

        list($xref, $pdfData) = $this->fixture->parseHeaderAndXref($pdf);

        $this->assertSame($pdf, $pdfData);
        $this->assertSame(
            [
                '1_0' => strpos($pdf, '1 0 obj'),
                '2_0' => strpos($pdf, '2 0 obj'),
                '4_0' => strpos($pdf, '4 0 obj'),
            ],
            $xref['xref']
        );
        $this->assertSame(5, $xref['trailer']['size']);
        $this->assertSame('1_0', $xref['trailer']['root']);

        // This information is available before any object gets decoded
        $this->assertSame([], $this->fixture->decodedObjects);
    }

    /**
     * Data in front of the header is cut off, because the byte offsets of the
     * cross-reference table are relative to the header.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=47 ISO 32000-1:2008, 7.5.2 (file header)
     */
    public function testParseHeaderAndXrefDataInFrontOfHeader(): void
    {
        $pdf = $this->createPdf($this->getObjects());

        list($xref, $pdfData) = $this->fixture->parseHeaderAndXref("data in front of header\n".$pdf);

        $this->assertSame($pdf, $pdfData);
        $this->assertSame(strpos($pdf, '4 0 obj'), $xref['xref']['4_0']);
    }

    /**
     * Byte offsets depend on the end-of-line markers of a file. If each \n of a
     * file is replaced by \r\n (e.g. by a transfer in text mode), its byte
     * offsets only fit again after the replacement is reverted.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=48 ISO 32000-1:2008, 7.5.4 (cross-reference table)
     * @see https://github.com/smalot/pdfparser/pull/635
     */
    public function testParseHeaderAndXrefLineEndings(): void
    {
        $pdf = $this->createPdf($this->getObjects());

        list($xref, $pdfData) = $this->fixture->parseHeaderAndXref(str_replace("\n", "\r\n", $pdf));

        $this->assertSame($pdf, $pdfData);
        $this->assertSame(strpos($pdf, '4 0 obj'), $xref['xref']['4_0']);

        // A file whose byte offsets fit to its \r\n end-of-line markers is left as it is
        $pdf = $this->createPdf($this->getObjects(), '', "\r\n");

        list($xref, $pdfData) = $this->fixture->parseHeaderAndXref($pdf);

        $this->assertSame($pdf, $pdfData);
        $this->assertSame(strpos($pdf, '4 0 obj'), $xref['xref']['4_0']);
    }

    /**
     * The Encrypt entry of the trailer is available without decoding an object,
     * which allows Parser to reject an encrypted file right away.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=51 ISO 32000-1:2008, 7.5.5, Table 15 (entries in the file trailer dictionary)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=63 ISO 32000-1:2008, 7.6.1 (encryption)
     */
    public function testParseHeaderAndXrefEncryptEntry(): void
    {
        list($xref) = $this->fixture->parseHeaderAndXref($this->createPdf($this->getObjects(), '/Encrypt 4 0 R '));

        $this->assertSame('4_0', $xref['trailer']['encrypt']);
        $this->assertSame([], $this->fixture->decodedObjects);
    }

    /**
     * An object gets decoded when the consumer asks for it.
     */
    public function testIterateIndirectObjectsDecodesOnDemand(): void
    {
        list($xref, $pdfData) = $this->fixture->parseHeaderAndXref($this->createPdf($this->getObjects()));

        $objects = $this->fixture->iterateIndirectObjects($pdfData, $xref);

        $this->assertInstanceOf(\Generator::class, $objects);
        $this->assertSame([], $this->fixture->decodedObjects);

        $this->assertSame('1_0', $objects->key());
        $this->assertSame(['1_0'], $this->fixture->decodedObjects);

        $objects->next();
        $this->assertSame('2_0', $objects->key());
        $this->assertSame(['1_0', '2_0'], $this->fixture->decodedObjects);
    }

    /**
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=29 ISO 32000-1:2008, 7.3.10 (indirect objects)
     */
    public function testIterateIndirectObjects(): void
    {
        $pdf = $this->createPdf($this->getObjects());
        list($xref, $pdfData) = $this->fixture->parseHeaderAndXref($pdf);

        $objects = iterator_to_array($this->fixture->iterateIndirectObjects($pdfData, $xref));

        // Objects are provided in the order of the cross-reference table
        $this->assertSame(['1_0', '2_0', '4_0'], array_keys($objects));

        // An element of a raw structure consists of type, value and the offset behind it
        $offset = strpos($pdf, '/Foo');
        $this->assertSame(
            [
                [
                    '<<',
                    [
                        ['/', 'Foo', $offset + 4],
                        ['(', 'bar', $offset + 10],
                    ],
                    $offset + 13,
                ],
            ],
            $objects['4_0']
        );
    }

    /**
     * Objects which are located in an object stream have no byte offset of
     * their own (type 2 entries of a cross-reference stream). They are left
     * out, Parser extracts them from the object stream they are located in.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=53 ISO 32000-1:2008, 7.5.7 (object streams)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=59 ISO 32000-1:2008, 7.5.8.3, Table 18 (types of cross-reference stream entries)
     */
    public function testIterateIndirectObjectsLeavesOutCompressedObjects(): void
    {
        $pdf = file_get_contents($this->rootDir.'/samples/bugs/Issue18.pdf');
        list($xref, $pdfData) = $this->fixture->parseHeaderAndXref($pdf);

        $objectsWithOffset = array_filter($xref['xref'], function ($offset) {
            return $offset > 0;
        });

        // Make sure the file contains compressed objects
        $this->assertContains(-1, $xref['xref']);
        $this->assertNotEmpty($objectsWithOffset);

        $objects = iterator_to_array($this->fixture->iterateIndirectObjects($pdfData, $xref));

        $this->assertSame(array_keys($objectsWithOffset), array_keys($objects));
    }

    /**
     * The key "xref" is only part of cross-reference data which contains an object in use.
     */
    public function testIterateIndirectObjectsWithoutObjects(): void
    {
        list($xref, $pdfData) = $this->fixture->parseHeaderAndXref($this->createPdf([]));

        $this->assertArrayNotHasKey('xref', $xref);

        // PHPUnit reports warnings and notices, but the test passes nevertheless.
        // Turn them into exceptions to let the test fail.
        set_error_handler(function (int $severity, string $message) {
            throw new \ErrorException($message, 0, $severity);
        });

        try {
            $objects = iterator_to_array($this->fixture->iterateIndirectObjects($pdfData, $xref));
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $objects);
    }

    /**
     * parseData() provides cross-reference data and all objects at once.
     */
    public function testParseData(): void
    {
        $pdf = $this->createPdf($this->getObjects());
        list($xref, $pdfData) = $this->fixture->parseHeaderAndXref($pdf);

        $result = $this->fixture->parseData($pdf);

        $this->assertCount(2, $result);
        $this->assertSame($xref, $result[0]);
        $this->assertSame(iterator_to_array($this->fixture->iterateIndirectObjects($pdfData, $xref)), $result[1]);
        $this->assertSame(['1_0', '2_0', '4_0'], array_keys($result[1]));
    }

    public function testParseDataEmptyData(): void
    {
        $this->expectException(EmptyPdfException::class);

        $this->fixture->parseData('');
    }

    /**
     * A realistic level of array nesting (far below anything pathological) parses
     * to full depth. A nesting-depth limit must stay above realistic depths so
     * legitimate PDFs keep the same parsed structure.
     */
    public function testModeratelyNestedArrayIsParsedToFullDepth(): void
    {
        $depth = 50;
        $result = $this->fixture->exposeGetRawObject(str_repeat('[', $depth).' 42 '.str_repeat(']', $depth));

        $this->assertSame($depth, $this->arrayDepth($result));
    }

    /**
     * Nesting depth of a getRawObject() array structure.
     */
    private function arrayDepth(array $node): int
    {
        if ('[' !== ($node[0] ?? null)) {
            return 0;
        }

        $max = 0;
        foreach ($node[1] as $child) {
            $max = max($max, $this->arrayDepth($child));
        }

        return 1 + $max;
    }
}

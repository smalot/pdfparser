<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2026-09-22
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
use Smalot\PdfParser\RawData\FilterHelper;

/**
 * Regression tests that the parser stays within bounded memory when it is given
 * pathological or malformed input: deeply nested objects, uncapped decompression
 * streams, and self-referential page trees.
 *
 * Each test asserts the behaviour the parser must have once the corresponding
 * limit is in place. Without that limit each one fails:
 *
 *   - the bounded-resource tests run the process out of memory, which is an
 *     uncatchable PHP fatal. Each lowers memory_limit first so the abort is fast
 *     and cheap instead of climbing to the suite's 1G ceiling. Because the fatal
 *     ends the PHP process, run them one at a time, e.g.
 *     phpunit --filter testDeeplyNestedArrayDoesNotExhaustMemory
 *   - testDecodeMemoryLimitIsHonoredByRunLengthDecode is an ordinary assertion
 *     failure (no process abort).
 *
 * @group robustness
 */
class RobustnessTest extends TestCase
{
    /**
     * @var string|false
     */
    private $originalMemoryLimit;

    protected function setUp(): void
    {
        parent::setUp();

        // Lower memory_limit so an unbounded run aborts quickly and cheaply. When a
        // memory-heavy test ran earlier and left usage above this value, PHP keeps
        // the higher limit; the '@' avoids the resulting notice. tearDown() restores
        // the original value so the shared PHP process keeps its configured limit
        // for the rest of the suite.
        $this->originalMemoryLimit = ini_get('memory_limit');
        @ini_set('memory_limit', '256M');
    }

    protected function tearDown(): void
    {
        ini_set('memory_limit', $this->originalMemoryLimit);

        parent::tearDown();
    }

    /**
     * A single object whose body is a deeply nested array makes
     * RawDataParser::getRawObject() recurse once per nesting level. With no depth
     * limit, a pathologically nested object exhausts memory (and, with more
     * headroom, the call stack). Reached by parseContent() alone.
     *
     * TO FIX: give getRawObject() a configurable maximum nesting depth (e.g. via
     * Config, with a sane default). When the limit is exceeded, stop descending
     * and return the partially parsed object instead of recursing further. Do not
     * throw — keep the library's lenient behaviour of returning incomplete results
     * rather than aborting the whole parse.
     */
    public function testDeeplyNestedArrayDoesNotExhaustMemory(): void
    {
        $depth = 150000;
        $pdf = $this->createPdf([
            1 => '<< /Type /Catalog >>',
            2 => str_repeat('[', $depth).str_repeat(']', $depth),
        ]);

        $document = $this->getParserInstance()->parseContent($pdf);

        // Reached only once getRawObject() is depth-bounded. Without a depth limit
        // the line above does not return (fatal at the recursion site).
        $this->assertInstanceOf(Document::class, $document);
    }

    /**
     * setDecodeMemoryLimit() bounds how far a single stream is decompressed, which
     * is the control against decompression amplification. With it set, a small
     * FlateDecode stream that would otherwise expand to hundreds of megabytes is
     * kept within the limit and parsing returns instead of exhausting memory.
     *
     * The default stays unlimited because a legitimate, highly compressible stream
     * (e.g. an image) cannot be told apart from a decompression bomb by size, so a
     * bounded default would truncate valid content.
     */
    public function testFlateDecodeIsBoundedWhenDecodeMemoryLimitSet(): void
    {
        // Built incrementally so the test itself never holds the expanded payload.
        $stream = $this->deflateZeroStream(300);
        $pdf = $this->createPdf([
            1 => '<< /Type /Catalog >>',
            2 => '<< /Length '.\strlen($stream)." /Filter /FlateDecode >>\nstream\n".$stream."\nendstream",
        ]);

        $config = new Config();
        $config->setDecodeMemoryLimit(10 * 1024 * 1024);

        $document = $this->getParserInstance($config)->parseContent($pdf);

        $this->assertInstanceOf(Document::class, $document);
    }

    /**
     * FilterHelper::decodeFilter() forwards $decodeMemoryLimit to FlateDecode but
     * not to LZWDecode or RunLengthDecode, so those filters expand their input
     * with no size limit even when one is configured. This test shows it for
     * RunLengthDecode without a process abort: the explicit limit is ignored.
     *
     * TO FIX: honour $decodeMemoryLimit in decodeFilterRunLengthDecode() AND
     * decodeFilterLZWDecode() — cap the output at the limit, or throw as
     * decodeFilterFlateDecode() does — and give them the same default bound.
     */
    public function testDecodeMemoryLimitIsHonoredByRunLengthDecode(): void
    {
        // "\x81\xFF" => 128 copies of 0xFF. 70000 pairs => ~8.75 MB decoded.
        $input = str_repeat("\x81\xff", 70000);
        $limit = 1024 * 1024; // 1 MB

        $filterHelper = new FilterHelper();

        try {
            $result = $filterHelper->decodeFilter('RunLengthDecode', $input, $limit);
        } catch (\Exception $e) {
            // Throwing when the limit is exceeded (like FlateDecode) is fine, too.
            $this->addToAssertionCount(1);

            return;
        }

        // It returned: with a limit set the output must not exceed it. (Kept
        // outside the try/catch above so PHPUnit's own assertion failure is not
        // swallowed by it.)
        $this->assertLessThanOrEqual(
            $limit,
            \strlen($result),
            'RunLengthDecode ignored the decode memory limit and returned an oversized string.'
        );
    }

    /**
     * A /Pages node whose /Kids references an ancestor (here: itself) makes
     * Pages::getPages() recurse indefinitely. Reached via Document::getPages() /
     * getText().
     *
     * TO FIX: track visited Pages nodes (or bound the depth) in Pages::getPages()
     * so a self-referential page tree cannot recurse forever; return the pages
     * collected so far.
     */
    public function testCyclicPageTreeDoesNotRecurseInfinitely(): void
    {
        $pdf = $this->createPdf([
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [2 0 R] /Count 1 >>', // Kids references itself
        ]);

        $document = $this->getParserInstance()->parseContent($pdf);

        // Triggers Pages::getPages() -> without a cycle guard this would recurse
        // infinitely.
        $pages = $document->getPages();

        $this->assertIsArray($pages);
    }

    /**
     * Build a zlib/deflate stream that expands to $mb megabytes of NUL bytes,
     * without ever holding the expanded data in memory.
     */
    private function deflateZeroStream(int $mb): string
    {
        $ctx = deflate_init(\ZLIB_ENCODING_DEFLATE, ['level' => 9]);
        $chunk = str_repeat("\0", 1024 * 1024); // 1 MB
        $out = '';
        for ($i = 0; $i < $mb; ++$i) {
            $out .= deflate_add($ctx, $chunk, \ZLIB_NO_FLUSH);
        }

        return $out.deflate_add($ctx, '', \ZLIB_FINISH);
    }
}

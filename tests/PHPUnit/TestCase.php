<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2020-06-02
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

namespace PHPUnitTests;

use PHPUnit\Framework\TestCase as PHPTestCase;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\Parser;

abstract class TestCase extends PHPTestCase
{
    /**
     * Contains an instance of the class to test.
     */
    protected $fixture;

    protected $rootDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rootDir = __DIR__.'/../..';
    }

    protected function tearDown(): void
    {
        $this->fixture = null;
        $this->rootDir = null;

        \gc_collect_cycles();
        if (\function_exists('gc_mem_caches')) {
            \gc_mem_caches();
        }

        parent::tearDown();
    }

    protected function getDocumentInstance(): Document
    {
        return new Document();
    }

    protected function getElementInstance($value): Element
    {
        return new Element($value);
    }

    protected function getParserInstance(?Config $config = null): Parser
    {
        return new Parser([], $config);
    }

    /**
     * @param array<int, array{0: float|null, 1: float|null}> $expectedPageDimensions
     */
    protected function assertDocumentPageCountAndDimensions(Document $document, array $expectedPageDimensions): void
    {
        $pages = $document->getPages();

        self::assertCount(\count($expectedPageDimensions), $pages);

        foreach ($pages as $index => $page) {
            self::assertInstanceOf(Page::class, $page);

            $dimension = $page->getDimensions('CropBox') ?? $page->getDimensions('MediaBox');

            [$expectedWidth, $expectedHeight] = $expectedPageDimensions[$index];

            if (null === $dimension || !isset($dimension['width'], $dimension['height'])) {
                // MediaBox is absent or unparseable in this fixture; skip dimension
                // assertions only when no specific value was expected.
                self::assertNull($expectedWidth, 'Unable to resolve MediaBox for page index '.$index.' (expected width '.$expectedWidth.').');
                self::assertNull($expectedHeight, 'Unable to resolve MediaBox for page index '.$index.' (expected height '.$expectedHeight.').');
                continue;
            }

            $width = (float) $dimension['width'];
            $height = (float) $dimension['height'];

            if (null === $expectedWidth) {
                self::assertGreaterThan(0.0, $width, 'Page width must be > 0 for page index '.$index.'.');
            } else {
                self::assertEqualsWithDelta($expectedWidth, $width, 0.01, 'Unexpected page width for page index '.$index.'.');
            }

            if (null === $expectedHeight) {
                self::assertGreaterThan(0.0, $height, 'Page height must be > 0 for page index '.$index.'.');
            } else {
                self::assertEqualsWithDelta($expectedHeight, $height, 0.01, 'Unexpected page height for page index '.$index.'.');
            }
        }
    }

    /**
     * @return array<int, array{0: null, 1: null}>
     */
    protected static function expectedPositivePageDimensions(int $pageCount): array
    {
        return array_fill(0, $pageCount, [null, null]);
    }

}

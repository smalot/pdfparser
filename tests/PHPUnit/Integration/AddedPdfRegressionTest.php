<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2026-04-30
 *
 * @license LGPLv3
 *
 * @url     <https://github.com/smalot/pdfparser>
 *
 * PdfParser is a pdf library written in PHP, extraction oriented.
 * Copyright (C) 2017 - Sebastien MALOT <sebastien@malot.fr>
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
use Smalot\PdfParser\Parser;

class AddedPdfRegressionTest extends TestCase
{
    /**
     * Regression coverage for PDFs added during PR unification.
     *
     * @dataProvider provideAddedPdfFixtures
     */
    public function testAddedPdfFixturePageCountRegression(string $relativePath, int $expectedPageCount): void
    {
        $fullPath = $this->rootDir.'/'.$relativePath;
        self::assertFileExists($fullPath);

        $document = (new Parser())->parseFile($fullPath);

        self::assertInstanceOf(Document::class, $document);
        self::assertCount($expectedPageCount, $document->getPages(), $relativePath);
    }

    /**
     * @return array<string, array{0:string, 1:int}>
     */
    public function provideAddedPdfFixtures(): array
    {
        return [
            'Brotli-Prototype-FileA' => ['samples/bugs/Brotli-Prototype-FileA.pdf', 1],
            'PullRequest797-pdf.js' => ['samples/bugs/PullRequest797-pdf.js.pdf', 1],
            'PullRequest797-vera' => ['samples/bugs/PullRequest797-vera.pdf', 1],
            'PullRequest806-pdf.js' => ['samples/bugs/PullRequest806-pdf.js.pdf', 2],
            'PullRequest812-issue7229' => ['samples/bugs/PullRequest812-issue7229.pdf', 2],
            'PullRequest813-pdf.js' => ['samples/bugs/PullRequest813-pdf.js.pdf', 1],
            'PullRequest814-pdf.js' => ['samples/bugs/PullRequest814-pdf.js.pdf', 1],
            'PullRequest815-xref-command-missing' => ['samples/bugs/PullRequest815-xref-command-missing.pdf', 1],
            'PullRequestDuplicateKids' => ['samples/bugs/PullRequestDuplicateKids.pdf', 1],
            'PullRequestInvalidObjectReference' => ['samples/bugs/PullRequestInvalidObjectReference.pdf', 1],
            'REDHAT-1531897-0' => ['samples/bugs/REDHAT-1531897-0.pdf', 0],
            'bug1978317' => ['samples/bugs/bug1978317.pdf', 1],
            'issue15590' => ['samples/bugs/issue15590.pdf', 1],
            'issue9105_other' => ['samples/bugs/issue9105_other.pdf', 1],
            'poppler-85140-0' => ['samples/bugs/poppler-85140-0.pdf', 1],
            'rawdata/PullRequest794' => ['samples/bugs/rawdata/PullRequest794.pdf', 1],
            'rawdata/PullRequest797-pdf.js' => ['samples/bugs/rawdata/PullRequest797-pdf.js.pdf', 1],
            'rawdata/PullRequest797-vera' => ['samples/bugs/rawdata/PullRequest797-vera.pdf', 1],
            'rawdata/PullRequest804-pdf.js' => ['samples/bugs/rawdata/PullRequest804-pdf.js.pdf', 1],
            'rawdata/PullRequest805-pdf.js' => ['samples/bugs/rawdata/PullRequest805-pdf.js.pdf', 3],
            'rawdata/PullRequest807-pdfjs-xref-missing-keyword' => ['samples/bugs/rawdata/PullRequest807-pdfjs-xref-missing-keyword.pdf', 1],
            'rawdata/PullRequest807-pdfjs-xref-startxref-misaligned' => ['samples/bugs/rawdata/PullRequest807-pdfjs-xref-startxref-misaligned.pdf', 5],
            'rawdata/PullRequest809-pdf.js' => ['samples/bugs/rawdata/PullRequest809-pdf.js.pdf', 1],
            'rawdata/PullRequest812-pdf.js' => ['samples/bugs/rawdata/PullRequest812-pdf.js.pdf', 1],
            'rawdata/PullRequest813-pdf.js' => ['samples/bugs/rawdata/PullRequest813-pdf.js.pdf', 1],
            'rawdata/PullRequest814-pdf.js' => ['samples/bugs/rawdata/PullRequest814-pdf.js.pdf', 1],
            'rawdata/PullRequest816-poppler-937-0-fuzzed' => ['samples/bugs/rawdata/PullRequest816-poppler-937-0-fuzzed.pdf', 1],
            'rawdata/PullRequest818-pdf.js' => ['samples/bugs/rawdata/PullRequest818-pdf.js.pdf', 0],
            'rawdata/PullRequestInvalidObjectReference' => ['samples/bugs/rawdata/PullRequestInvalidObjectReference.pdf', 1],
            'rawdata/PullRequestNearbyObjectHeaderOffset' => ['samples/bugs/rawdata/PullRequestNearbyObjectHeaderOffset.pdf', 1],
            'rawdata/PullRequestXrefSubsectionMultipleSpaces' => ['samples/bugs/rawdata/PullRequestXrefSubsectionMultipleSpaces.pdf', 1],
            'rawdata/bug1250079' => ['samples/bugs/rawdata/bug1250079.pdf', 1],
            'rawdata/bug1539074.1' => ['samples/bugs/rawdata/bug1539074.1.pdf', 1],
            'rawdata/bug1539074' => ['samples/bugs/rawdata/bug1539074.pdf', 1],
            'rawdata/bug1606566' => ['samples/bugs/rawdata/bug1606566.pdf', 1],
            'rawdata/bug1795263' => ['samples/bugs/rawdata/bug1795263.pdf', 1],
            'rawdata/named_dest_collision_for_editor' => ['samples/bugs/rawdata/named_dest_collision_for_editor.pdf', 1],
            'rawdata/pdfjs-issue19517' => ['samples/bugs/rawdata/pdfjs-issue19517.pdf', 1],
            'rawdata/poppler-742-0-fuzzed' => ['samples/bugs/rawdata/poppler-742-0-fuzzed.pdf', 1],
        ];
    }
}

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
     * Creates a minimal PDF file with a cross-reference table. The byte offsets
     * of the objects and of the cross-reference table are calculated.
     *
     * @param array<int, string> $objects        object number => content of the object, in ascending order;
     *                                           numbers which are left out become free entries
     * @param string             $trailerEntries additional entries of the trailer dictionary
     * @param string             $eol            end-of-line marker used in the whole file
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=46 ISO 32000-1:2008, 7.5 (file structure)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=48 ISO 32000-1:2008, 7.5.4 (cross-reference table)
     */
    protected function createPdf(array $objects, string $trailerEntries = '', string $eol = "\n"): string
    {
        $pdf = '%PDF-1.4'.$eol;

        $offsets = [];
        foreach ($objects as $number => $content) {
            $offsets[$number] = \strlen($pdf);
            $pdf .= $number.' 0 obj'.$eol.$content.$eol.'endobj'.$eol;
        }

        // An entry is 20 bytes long, including its end-of-line marker
        $entryEol = 2 === \strlen($eol) ? $eol : ' '.$eol;
        $size = [] === $objects ? 1 : max(array_keys($objects)) + 1;

        $startxref = \strlen($pdf);
        $pdf .= 'xref'.$eol.'0 '.$size.$eol.'0000000000 65535 f'.$entryEol;
        for ($number = 1; $number < $size; ++$number) {
            $pdf .= isset($offsets[$number])
                ? sprintf('%010d 00000 n', $offsets[$number]).$entryEol
                : '0000000000 00001 f'.$entryEol;
        }

        return $pdf.'trailer'.$eol.'<< /Size '.$size.' /Root 1 0 R '.$trailerEntries.'>>'.$eol
            .'startxref'.$eol.$startxref.$eol.'%%EOF';
    }
}

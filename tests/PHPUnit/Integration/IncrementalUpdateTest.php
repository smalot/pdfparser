<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Marek Gralikowski <marek@ebizo.pl>
 *
 * @date    2026-09-29
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
use Smalot\PdfParser\Parser;

/**
 * An incremental update (ISO 32000-1, 7.5.6) appends new revisions of objects; the newest
 * cross-reference section decides which revision is current. Object streams (7.5.7) of an
 * older revision keep carrying the stale copies.
 */
class IncrementalUpdateTest extends TestCase
{
    /**
     * Revision 1 keeps the page tree compressed in an object stream, revision 2 rewrites it
     * uncompressed and adds a page - the shape macOS Preview produces when saving an edited file.
     */
    public function testUncompressedRevisionSupersedesObjectStreamCopy(): void
    {
        $pdf = $this->buildTwoRevisionPdf(false);

        $pages = (new Parser())->parseContent($pdf)->getPages();

        $this->assertCount(2, $pages);
    }

    /**
     * The reverse: revision 1 is uncompressed, revision 2 moves the updated page tree into an object stream.
     */
    public function testObjectStreamRevisionSupersedesUncompressedCopy(): void
    {
        $pdf = $this->buildTwoRevisionPdf(true);

        $pages = (new Parser())->parseContent($pdf)->getPages();

        $this->assertCount(2, $pages);
    }

    /**
     * Two-page PDF whose second page arrives in an incremental update; object 2 is the /Pages node.
     *
     * @param bool $compressedUpdate true: revision 1 stores /Pages uncompressed and revision 2 in an
     *                               object stream; false: the other way round
     */
    private function buildTwoRevisionPdf(bool $compressedUpdate): string
    {
        $pdf = "%PDF-1.5\n";
        $offsets = [];
        $append = function (int $number, string $body) use (&$pdf, &$offsets): void {
            $offsets[$number] = \strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$body}\nendobj\n";
        };
        $stream = function (string $dictionary, string $data): string {
            return "<< {$dictionary} /Length ".\strlen($data)." >>\nstream\n{$data}\nendstream";
        };
        $row = function (int $type, int $field2, int $field3): string {
            return pack('CNn', $type, $field2, $field3);
        };
        $oldPages = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $newPages = '<< /Type /Pages /Kids [3 0 R 4 0 R] /Count 2 >>';

        // Revision 1: objects 1 (catalog), 2 (pages), 3 (page), 5 (object stream or unused), 6 (xref stream)
        $append(1, '<< /Type /Catalog /Pages 2 0 R >>');
        $append(3, '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << >> >>');
        if ($compressedUpdate) {
            $append(2, $oldPages);
            $pagesEntry = $row(1, $offsets[2], 0);
            $fiveEntry = $row(0, 0, 0);
        } else {
            $append(5, $stream('/Type /ObjStm /N 1 /First 4', '2 0 '.$oldPages));
            $pagesEntry = $row(2, 5, 0);
            $fiveEntry = $row(1, $offsets[5], 0);
        }
        $offsets[6] = \strlen($pdf);
        $append(6, $stream('/Type /XRef /Size 7 /W [1 4 2] /Root 1 0 R', implode('', [
            $row(0, 0, 65535),
            $row(1, $offsets[1], 0),
            $pagesEntry,
            $row(1, $offsets[3], 0),
            $row(0, 0, 0),
            $fiveEntry,
            $row(1, $offsets[6], 0),
        ])));
        $pdf .= "startxref\n{$offsets[6]}\n%%EOF\n";

        // Revision 2: page 4 and the updated /Pages node (object 2), the latter either
        // uncompressed or inside the new object stream 8.
        $append(4, '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << >> >>');
        if ($compressedUpdate) {
            $append(8, $stream('/Type /ObjStm /N 1 /First 4', '2 0 '.$newPages));
            $offsets[7] = \strlen($pdf);
            $append(7, $stream("/Type /XRef /Size 9 /Index [2 1 4 1 7 2] /W [1 4 2] /Root 1 0 R /Prev {$offsets[6]}", implode('', [
                $row(2, 8, 0),
                $row(1, $offsets[4], 0),
                $row(1, $offsets[7], 0),
                $row(1, $offsets[8], 0),
            ])));
        } else {
            $append(2, $newPages);
            $offsets[7] = \strlen($pdf);
            $append(7, $stream("/Type /XRef /Size 8 /Index [2 1 4 1 7 1] /W [1 4 2] /Root 1 0 R /Prev {$offsets[6]}", implode('', [
                $row(1, $offsets[2], 0),
                $row(1, $offsets[4], 0),
                $row(1, $offsets[7], 0),
            ])));
        }

        return $pdf."startxref\n{$offsets[7]}\n%%EOF\n";
    }
}

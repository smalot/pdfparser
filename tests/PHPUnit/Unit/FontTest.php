<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2023-07-19
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

namespace PHPUnitTests\Unit;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Encoding;
use Smalot\PdfParser\Font;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\PDFObject;

class FontTest extends TestCase
{
    /**
     * Font::getDetails() must not throw when Encoding is an indirect reference
     * that resolves to a PDFObject instead of an Element.
     *
     * Such PDFs store the Encoding as an object reference (e.g. "12 0 R") whose
     * resolved target is a plain PDFObject without /Type /Encoding — a valid
     * structure per PDF spec Table 5.11 (encoding dictionary with /Differences).
     *
     * @see https://github.com/smalot/pdfparser/issues/822
     */
    public function testGetDetailsEncodingAsPDFObjectWithBaseEncoding(): void
    {
        $document = new Document();
        $encodingObj = new PDFObject(
            $document,
            new Header(['BaseEncoding' => new Element('WinAnsiEncoding')])
        );
        $font = new Font($document, new Header(['Encoding' => $encodingObj]));

        $details = $font->getDetails(false);

        self::assertSame('WinAnsiEncoding', $details['Encoding']);
    }

    /**
     * When Encoding is a PDFObject without a BaseEncoding entry the font uses
     * its built-in encoding as base (PDF spec §5.5.5). getDetails() must return
     * 'Ansi' as fallback, consistent with Encoding::getDetails()['BaseEncoding'].
     */
    public function testGetDetailsEncodingAsPDFObjectWithoutBaseEncoding(): void
    {
        $document = new Document();
        $encodingObj = new PDFObject($document, new Header([]));
        $font = new Font($document, new Header(['Encoding' => $encodingObj]));

        $details = $font->getDetails(false);

        self::assertSame('Ansi', $details['Encoding']);
    }

    /**
     * When Encoding is an Encoding instance (PDFObject subclass, /Type /Encoding
     * present) the BaseEncoding name must be returned.
     */
    public function testGetDetailsEncodingAsEncodingInstance(): void
    {
        $document = new Document();
        $encodingObj = new Encoding(
            $document,
            new Header(['BaseEncoding' => new Element('MacRomanEncoding')])
        );
        $font = new Font($document, new Header(['Encoding' => $encodingObj]));

        $details = $font->getDetails(false);

        self::assertSame('MacRomanEncoding', $details['Encoding']);
    }

    /**
     * When Encoding is a direct name element (e.g. /WinAnsiEncoding) the name
     * is returned as-is — the original pre-fix behaviour must be preserved.
     */
    public function testGetDetailsEncodingAsDirectElement(): void
    {
        $document = new Document();
        $font = new Font(
            $document,
            new Header(['Encoding' => new Element('WinAnsiEncoding')])
        );

        $details = $font->getDetails(false);

        self::assertSame('WinAnsiEncoding', $details['Encoding']);
    }

    /**
     * When no Encoding entry is present getDetails() must return 'Ansi'.
     */
    public function testGetDetailsEncodingMissingDefaultsToAnsi(): void
    {
        $document = new Document();
        $font = new Font($document, new Header([]));

        $details = $font->getDetails(false);

        self::assertSame('Ansi', $details['Encoding']);
    }

    /**
     * decodeText must decode \b.
     *
     * @see https://github.com/smalot/pdfparser/pull/597
     */
    public function testDecodeTextIssue597(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('getFontSpaceLimit')->willReturn(1);

        $document = $this->createMock(Document::class);
        $sut = new Font($document, null, null, $config);

        $commands = [
            [
                PDFObject::TYPE => '<',
                PDFObject::COMMAND => "<ab>\b",
            ],
        ];

        // result is a binary string and looks like: 0x3cc2ab083e
        $result = $sut->decodeText($commands);

        // check that \b is not part of the result anymore
        self::assertFalse(strpos($result, "\b>"));

        // compare result with expected value
        self::assertEquals('3cc2ab083e', bin2hex($result));
    }
}

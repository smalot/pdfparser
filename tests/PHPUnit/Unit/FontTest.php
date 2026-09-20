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
use Smalot\PdfParser\Font;
use Smalot\PdfParser\PDFObject;

class FontTest extends TestCase
{
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

    /**
     * hexdec() returns a float larger than PHP_INT_MAX for oversized hex
     * strings. Casting such a float to int raises a "not representable as int"
     * warning on PHP 8.5+; older PHP versions silently produced a wrapped-around
     * (possibly negative) integer or 0, which uchr() then turned into a NUL byte
     * or a literal "&#-123;" string.
     *
     * Since these values can not represent valid Unicode code points anyway,
     * uchr() has to return Font::MISSING for them, on every PHP version.
     *
     * @see https://github.com/smalot/pdfparser/pull/623
     * @see https://github.com/smalot/pdfparser/pull/825
     */
    public function testUchrWithOutOfRangeFloat(): void
    {
        // a regular code point is still decoded
        $this->assertSame('A', Font::uchr(0x41));

        // a float that fits into an integer is still cast and decoded; this is
        // the reason uchr() accepts floats in the first place
        $this->assertSame('A', Font::uchr(65.0));

        // the highest Unicode code point as float must be cast, not treated as missing
        $this->assertSame(Font::uchr(0x10FFFF), Font::uchr((float) 0x10FFFF));

        // floats that do not fit into an integer can never be a valid code
        // point; the value below is produced by hexdec() of an oversized hex
        // string taken from samples/bugs/Issue621.pdf
        $this->assertSame(Font::MISSING, Font::uchr(1.50646556872121E+28));
        $this->assertSame(Font::MISSING, Font::uchr(-1.0E+30));
        $this->assertSame(Font::MISSING, Font::uchr(\INF));
        $this->assertSame(Font::MISSING, Font::uchr(-\INF));
        $this->assertSame(Font::MISSING, Font::uchr(\NAN));

        // boundary: PHP_INT_MAX + 1 (= 2^63 on 64-bit) is the smallest float
        // that does not fit anymore. It is exactly what hexdec() returns for
        // <8000000000000000>. A naive "$code > PHP_INT_MAX" check misses it,
        // because PHP_INT_MAX is rounded up to the very same float in the comparison.
        $this->assertSame(Font::MISSING, Font::uchr(\PHP_INT_MAX + 1));
        $this->assertSame(Font::MISSING, Font::uchr(hexdec('8000000000000000')));
        $this->assertSame(Font::MISSING, Font::uchr(hexdec('FFFFFFFFFFFFFFFF')));
    }
}

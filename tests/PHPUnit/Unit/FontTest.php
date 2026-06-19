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
     * A CMap could contain oversized hex values. hexdec() then returns a float
     * larger than PHP_INT_MAX which cannot be cast to int. On PHP 8.5 this
     * cast raises a "not representable as int" warning.
     *
     * Since these values can not represent valid Unicode code points anyway,
     * it's safe to return Font::MISSING for them. This test checks that this
     * is the case.
     *
     * The test relies on PhpUnit's failOnWarning="true" in phpunit.xml:
     * a warning would error.
     *
     * @see https://github.com/smalot/pdfparser/pull/623
     * @see https://github.com/smalot/pdfparser/pull/825
     */
    public function testUchrWithOutOfRangeFloat(): void
    {
        // a regular code point is still decoded
        $this->assertEquals('A', Font::uchr(0x41));

        // a float that fits into an integer is still cast and decoded; this is
        // the reason uchr() accepts floats in the first place
        $this->assertEquals('A', Font::uchr(65.0));

        // floats that do not fit into an integer can never be a valid code
        // point; the value below is produced by hexdec() of an oversized hex
        // string taken from samples/bugs/Issue621.pdf
        $this->assertEquals(Font::MISSING, Font::uchr(1.50646556872121E+28));
        $this->assertEquals(Font::MISSING, Font::uchr(\INF));
        $this->assertEquals(Font::MISSING, Font::uchr(\NAN));
    }
}

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
}

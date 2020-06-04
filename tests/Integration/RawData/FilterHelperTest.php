<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2020-06-01
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
 *
 * @license LGPLv3
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

namespace Tests\Smalot\PdfParser\Integration\RawData;

use Exception;
use Smalot\PdfParser\RawData\FilterHelper;
use Test\Smalot\PdfParser\TestCase;

class FilterHelperTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new FilterHelper();
    }

    public function testDecodeFilterFlateDecode()
    {
        $compressed = gzcompress('Compress me', 9);
        $result = $this->fixture->decodeFilter('FlateDecode', $compressed);

        $this->assertEquals('Compress me', $result);
    }

    /**
     * How does function behave if an empty string was given.
     */
    public function testDecodeFilterFlateDecodeEmptyString()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('gzuncompress(): data error');

        $this->fixture->decodeFilter('FlateDecode', '');
    }

    /**
     * How does function behave if an uncompressed string was given.
     */
    public function testDecodeFilterFlateDecodeUncompressedString()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('gzuncompress(): data error');

        $this->fixture->decodeFilter('FlateDecode', 'something');
    }
}

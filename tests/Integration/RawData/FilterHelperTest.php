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
use Tests\Smalot\PdfParser\TestCase;

class FilterHelperTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new FilterHelper();
    }

    /*
     * Tests for filter ASCII85Decode
     */

    public function testDecodeFilterASCII85Decode()
    {
        $compressed = '6Z6g\Eb0<5ARlp)FE2)5B)'; // = Compressed string
        $result = $this->fixture->decodeFilter('ASCII85Decode', $compressed);

        $this->assertEquals('Compressed string', $result);
    }

    /*
     * Tests for filter ASCIIHexDecode
     */

    public function testDecodeFilterASCIIHexDecode()
    {
        $compressed = '43 6f 6d 70 72 65 73 73 65 64 20 73 74 72 69 6e 67'; // = Compressed string
        $result = $this->fixture->decodeFilter('ASCIIHexDecode', $compressed);

        $this->assertEquals('Compressed string', $result);
    }

    /*
     * Tests for filter FlateDecode
     */

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

    /**
     * How does function behave if an uncompressed string was given.
     */
    public function testDecodeFilterUnknownFilter()
    {
        $result = $this->fixture->decodeFilter('a string '.rand(), 'something');
        $this->assertEquals('something', $result);
    }

    /*
     * Test for filters not being implemented yet.
     */

    /**
     * CCITTFaxDecode
     */
    public function testDecodeFilterCCITTFaxDecode()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Decode CCITTFaxDecode not implemented yet.');

        $this->fixture->decodeFilter('CCITTFaxDecode', '');
    }

    /**
     * Crypt
     */
    public function testDecodeFilterCrypt()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Decode Crypt not implemented yet.');

        $this->fixture->decodeFilter('Crypt', '');
    }

    /**
     * DCTDecode
     */
    public function testDecodeFilterDCTDecode()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Decode DCTDecode not implemented yet.');

        $this->fixture->decodeFilter('DCTDecode', '');
    }

    /**
     * JBIG2Decode
     */
    public function testDecodeFilterJBIG2Decode()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Decode JBIG2Decode not implemented yet.');

        $this->fixture->decodeFilter('JBIG2Decode', '');
    }

    /**
     * JPXDecode
     */
    public function testDecodeFilterJPXDecode()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Decode JPXDecode not implemented yet.');

        $this->fixture->decodeFilter('JPXDecode', '');
    }
}

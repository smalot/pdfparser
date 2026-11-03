<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2020-06-01
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

namespace PHPUnitTests\Integration\RawData;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\RawData\FilterHelper;

class FilterHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new FilterHelper();
    }

    /*
     * Tests for filter ASCII85Decode
     */

    public function testDecodeFilterASCII85Decode(): void
    {
        $compressed = '6Z6g\Eb0<5ARlp)FE2)5B)'; // = Compressed string
        $result = $this->fixture->decodeFilter('ASCII85Decode', $compressed);

        $this->assertEquals('Compressed string', $result);
    }

    public function testDecodeFilterASCII85DecodeInitSequence(): void
    {
        $compressed = '<~6Z6g\Eb0<5ARlp)FE2)5B)'; // = Compressed string
        $result = $this->fixture->decodeFilter('ASCII85Decode', $compressed);

        $this->assertEquals('Compressed string', $result);
    }

    public function testDecodeFilterASCII85DecodeEndSequence(): void
    {
        $compressed = '6Z6g\Eb0<5ARlp)FE2)5B)~>'; // = Compressed string
        $result = $this->fixture->decodeFilter('ASCII85Decode', $compressed);

        $this->assertEquals('Compressed string', $result);
    }

    public function testDecodeFilterASCII85DecodeSpecificEndSequence(): void
    {
        $compressed = '+^6b<~>'; // = 0x215B33C0 = "![3\xC0"
        $result = $this->fixture->decodeFilter('ASCII85Decode', $compressed);

        $this->assertEquals("\x21\x5B\x33\xC0", $result);
    }

    /*
     * Tests for filter ASCIIHexDecode
     */

    public function testDecodeFilterASCIIHexDecode(): void
    {
        $compressed = '43 6f 6d 70 72 65 73 73 65 64 20 73 74 72 69 6e 67'; // = Compressed string
        $result = $this->fixture->decodeFilter('ASCIIHexDecode', $compressed);

        $this->assertEquals('Compressed string', $result);
    }

    /*
     * Tests for filter FlateDecode
     */

    public function testDecodeFilterFlateDecode(): void
    {
        $compressed = gzcompress('Compress me', 9);
        $result = $this->fixture->decodeFilter('FlateDecode', $compressed);

        $this->assertEquals('Compress me', $result);
    }

    /**
     * How does function behave if an empty string was given.
     */
    public function testDecodeFilterFlateDecodeEmptyString(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('decodeFilterFlateDecode: invalid data');

        $this->fixture->decodeFilter('FlateDecode', '');
    }

    /**
     * How does function behave if an uncompressed string was given.
     */
    public function testDecodeFilterFlateDecodeUncompressedString(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('decodeFilterFlateDecode: invalid data');

        $this->fixture->decodeFilter('FlateDecode', 'something');
    }

    /**
     * How does function behave if compression checksum is CRC32 instead of Adler-32.
     * See: https://github.com/smalot/pdfparser/issues/592
     */
    public function testDecodeFilterFlateDecodeCRC32Checksum(): void
    {
        $document = (new Parser())->parseFile($this->rootDir.'/samples/bugs/Issue592.pdf');

        self::assertStringContainsString('Two Westbrook Corporate Center Suite 500', $document->getText());
    }

    /*
     * Tests for filter LZWDecode
     */

    /**
     * Example from ISO 32000-1:2008, 7.4.4.2: codes 256 45 258 258 65 259 66 257.
     * The first 258 is the entry being added (the "KwKwK" case).
     */
    public function testDecodeFilterLZWDecode(): void
    {
        $result = $this->fixture->decodeFilter('LZWDecode', "\x80\x0B\x60\x50\x22\x0C\x0C\x85\x01");

        $this->assertSame('-----A---B', $result);
    }

    /**
     * A run of one byte hits the "KwKwK" case at almost every code.
     */
    public function testDecodeFilterLZWDecodeRepeatedByte(): void
    {
        // 256 97 258 259 97 257: "aaaaaaaaaa"
        $result = $this->fixture->decodeFilter('LZWDecode', "\x80\x18\x60\x50\x38\x24\x04");
        $this->assertSame(str_repeat('a', 10), $result);

        $data = str_repeat('a', 5000).str_repeat('ab', 5000);
        $result = $this->fixture->decodeFilter('LZWDecode', $this->encodeLZW($data));
        $this->assertSame($data, $result);
    }

    /**
     * How does function behave if an unknown filter name was given.
     */
    public function testDecodeFilterUnknownFilter(): void
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
    public function testDecodeFilterCCITTFaxDecode(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Decode CCITTFaxDecode not implemented yet.');

        $this->fixture->decodeFilter('CCITTFaxDecode', '');
    }

    /**
     * Crypt
     */
    public function testDecodeFilterCrypt(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Decode Crypt not implemented yet.');

        $this->fixture->decodeFilter('Crypt', '');
    }

    /**
     * DCTDecode
     */
    public function testDecodeFilterDCTDecode(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Decode DCTDecode not implemented yet.');

        $this->fixture->decodeFilter('DCTDecode', '');
    }

    /**
     * JBIG2Decode
     */
    public function testDecodeFilterJBIG2Decode(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Decode JBIG2Decode not implemented yet.');

        $this->fixture->decodeFilter('JBIG2Decode', '');
    }

    /**
     * JPXDecode
     */
    public function testDecodeFilterJPXDecode(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Decode JPXDecode not implemented yet.');

        $this->fixture->decodeFilter('JPXDecode', '');
    }

    /*
     * Tests for filter RunLengthDecode
     */

    /**
     * A valid RunLength stream decodes to the same bytes independently of any
     * decode memory limit: "\x02ABC" copies 3 literal bytes, "\xFEX" repeats 'X'
     * three times and "\x80" is the EOD marker. An output-size bound added to this
     * filter must leave small, valid streams like this unchanged.
     */
    public function testDecodeFilterRunLengthDecode(): void
    {
        $result = $this->fixture->decodeFilter('RunLengthDecode', "\x02ABC\xFEX\x80");

        $this->assertSame('ABCXXX', $result);
    }

    /**
     * Minimal LZW encoder per ISO 32000-1:2008, 7.4.4.2 (EarlyChange 1).
     * Starts with a clear-table code, clears again when the table is full,
     * ends with EOD.
     */
    private function encodeLZW(string $data): string
    {
        $bits = '';
        $width = 9;
        $table = [];
        $next = 258;
        $emit = function (int $code) use (&$bits, &$width) {
            $bits .= str_pad(decbin($code), $width, '0', \STR_PAD_LEFT);
        };
        $reset = function () use (&$table, &$next, &$width) {
            $table = [];
            for ($i = 0; $i < 256; ++$i) {
                $table[\chr($i)] = $i;
            }
            $next = 258;
            $width = 9;
        };

        $reset();
        $emit(256);
        $current = '';
        $length = \strlen($data);
        for ($i = 0; $i < $length; ++$i) {
            $extended = $current.$data[$i];
            if (isset($table[$extended])) {
                $current = $extended;
                continue;
            }
            $emit($table[$current]);
            $table[$extended] = $next++;
            // EarlyChange 1: widen the codes one code early
            if ($next + 1 > (1 << $width) && $width < 12) {
                ++$width;
            }
            if ($next >= 4095) {
                $emit(256);
                $reset();
            }
            $current = $data[$i];
        }
        if ('' !== $current) {
            $emit($table[$current]);
        }
        $emit(257);

        $bits .= str_repeat('0', (8 - \strlen($bits) % 8) % 8);
        $encoded = '';
        foreach (str_split($bits, 8) as $byte) {
            $encoded .= \chr(bindec($byte));
        }

        return $encoded;
    }
}

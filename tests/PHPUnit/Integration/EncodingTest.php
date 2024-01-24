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

namespace PHPUnitTests\Integration;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Encoding;
use Smalot\PdfParser\Encoding\StandardEncoding;
use Smalot\PdfParser\Exception\EncodingNotFoundException;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Parser;

class EncodingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new Parser();
    }

    public function testGetEncodingClass(): void
    {
        $header = new Header(['BaseEncoding' => new Element('StandardEncoding')]);

        $encoding = new Encoding(new Document(), $header);
        $encoding->init();

        $this->assertEquals('\\'.StandardEncoding::class, $encoding->__toString());
    }

    /**
     * This tests checks behavior if given Encoding class doesn't exist.
     *
     * Protected method getEncodingClass is called in init and __toString.
     * It throws an exception if class is not available.
     * Calling init is enough to trigger the exception, but __toString call afterwards
     * makes sure that we don't missing it.
     */
    public function testInitGetEncodingClassMissingClassException(): void
    {
        $this->expectException(EncodingNotFoundException::class);
        $this->expectExceptionMessage('Missing encoding data for: "invalid"');

        $header = new Header(['BaseEncoding' => new Element('invalid')]);

        $encoding = new Encoding(new Document(), $header);
        $encoding->init();

        $encoding->__toString();
    }

    /**
     * This tests focuses on behavior of Encoding::__toString when running PHP 7.4+ and prior.
     *
     * Prior PHP 7.4 we expect an empty string to be returned (based on PHP specification).
     * PHP 7.4+ we expect an exception to be thrown when class is invalid.
     */
    public function testToStringGetEncodingClassMissingClassException(): void
    {
        // prior to PHP 7.4 toString has to return an empty string.
        if (version_compare(\PHP_VERSION, '7.4.0', '<')) {
            $header = new Header(['BaseEncoding' => new Element('invalid')]);

            $encoding = new Encoding(new Document(), $header);

            $this->assertEquals('', $encoding->__toString());
        } else {
            // PHP 7.4+
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Missing encoding data for: "invalid"');

            $header = new Header(['BaseEncoding' => new Element('invalid')]);

            $encoding = new Encoding(new Document(), $header);

            $encoding->__toString();
        }
    }

    /**
     * Fall back to 'StandardEncoding' when the document has none
     *
     * @see https://github.com/smalot/pdfparser/issues/665
     */
    public function testEmptyBaseEncodingFallback(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue665.pdf';

        $document = $this->fixture->parseFile($filename);
        $objects = $document->getObjects();

        $this->assertEquals(25, \count($objects));
        $this->assertArrayHasKey('3_0', $objects);
    }
}

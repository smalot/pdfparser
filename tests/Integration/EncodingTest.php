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

namespace Tests\Smalot\PdfParser\Integration;

use Exception;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Encoding;
use Smalot\PdfParser\Encoding\StandardEncoding;
use Smalot\PdfParser\Header;
use Tests\Smalot\PdfParser\TestCase;

class EncodingTest extends TestCase
{
    public function testGetEncodingClass()
    {
        $header = new Header(['BaseEncoding' => new Element('StandardEncoding')]);

        $encoding = new Encoding(new Document(), $header);
        $encoding->init();

        $this->assertEquals('\\'.StandardEncoding::class, $encoding->__toString());
    }

    /**
     * This tests checks behavior if given Encoding class doesn't exist.
     */
    public function testGetEncodingClassMissingClassException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing encoding data for: "invalid"');

        $header = new Header(['BaseEncoding' => new Element('invalid')]);

        $encoding = new Encoding(new Document(), $header);
        $encoding->init();

        $encoding->__toString();
    }
}

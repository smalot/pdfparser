<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2020-06-02
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

namespace Tests\Smalot\PdfParser\Integration\Element;

use DateTime;
use Exception;
use Smalot\PdfParser\Element\ElementDate;
use Test\Smalot\PdfParser\TestCase;

class ElementDateTest extends TestCase
{
    public function testParse()
    {
        // Skipped.
        $offset = 0;
        $element = ElementDate::parse('ABC', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementDate::parse(' [ (ABC) 5 6 ]', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementDate::parse(' << (invalid) >>', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementDate::parse(' / (FlateDecode) ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementDate::parse(' 0 (FlateDecode) ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementDate::parse(" 0 \n (FlateDecode) ", null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        // Valid.
        $offset = 0;
        $element = ElementDate::parse(' (D:20130901235555+02\'00\') ', null, $offset);
        $element->setFormat('c');
        $this->assertTrue($element->getContent() instanceof DateTime);
        $this->assertEquals('2013-09-01T23:55:55+02:00', (string) $element);
        $this->assertEquals(26, $offset);

        $offset = 0;
        $element = ElementDate::parse(' (D:20130901235555+02\'00\') ', null, $offset);
        $element->setFormat('c');
        $this->assertTrue($element->getContent() instanceof DateTime);
        $this->assertEquals('2013-09-01T23:55:55+02:00', (string) $element);
        $this->assertEquals(26, $offset);

        $offset = 0;
        $element = ElementDate::parse(' (D:20130901235555+02\'00\')', null, $offset);
        $element->setFormat('c');
        $this->assertTrue($element->getContent() instanceof DateTime);
        $this->assertEquals('2013-09-01T23:55:55+02:00', (string) $element);
        $this->assertEquals(26, $offset);

        $offset = 0;
        $element = ElementDate::parse('(D:20130901235555+02\'00\')', null, $offset);
        $element->setFormat('c');
        $this->assertTrue($element->getContent() instanceof DateTime);
        $this->assertEquals('2013-09-01T23:55:55+02:00', (string) $element);
        $this->assertEquals(25, $offset);

        $offset = 0;
        $element = ElementDate::parse(" \n (D:20130901235555+02'00') ", null, $offset);
        $element->setFormat('c');
        $this->assertTrue($element->getContent() instanceof DateTime);
        $this->assertEquals('2013-09-01T23:55:55+02:00', (string) $element);
        $this->assertEquals(28, $offset);

        $offset = 0;
        $element = ElementDate::parse(" \n (D:20130901235555) ", null, $offset);
        $element->setFormat('c');
        $this->assertTrue($element->getContent() instanceof DateTime);
        $this->assertEquals('2013-09-01T23:55:55+00:00', (string) $element);
        $this->assertEquals(21, $offset);

        $offset = 0;
        $element = ElementDate::parse("(D:20131206091846Z00'00')", null, $offset);
        $element->setFormat('c');
        $this->assertTrue($element->getContent() instanceof DateTime);
        $this->assertEquals('2013-12-06T09:18:46+00:00', (string) $element);
        $this->assertEquals(25, $offset);

        $offset = 0;
        $element = ElementDate::parse(" \n (D:1-23-2014, 19:02:15-03'00') ", null, $offset);
        $element->setFormat('c');
        $this->assertTrue($element->getContent() instanceof DateTime);
        $this->assertEquals('2014-01-23T19:02:15-03:00', (string) $element);
        $this->assertEquals(33, $offset);

        // Format invalid
        $offset = 0;
        $element = ElementDate::parse(" \n (D:2013+02'00') ", null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);
    }

    public function testGetContent()
    {
        $element = new ElementDate(new DateTime('2013-09-01 23:55:55+02:00'));
        $this->assertEquals(new DateTime('2013-09-01 21:55:55+00:00'), $element->getContent());
    }

    public function testGetContentInvalidParameter()
    {
        $this->expectException(Exception::class);

        $element = new ElementDate('2013-09-01 23:55:55+02:00');
        $this->assertEquals(new DateTime('2013-09-01 21:55:55+02:00'), $element->getContent());
    }

    public function testEquals()
    {
        $element = new ElementDate(new DateTime('2013-09-01 23:55:55+02:00'));
        $element->setFormat('c');

        $this->assertTrue($element->equals('2013-09-01T23:55:55+02:00'));
        $this->assertFalse($element->equals('2013-09-01T23:55:55+01:00'));

        $this->assertTrue($element->equals(new DateTime('2013-09-01T21:55:55+00:00')));
        $this->assertFalse($element->equals(new DateTime('2013-09-01T23:55:55+01:00')));

        $this->assertFalse($element->equals('ABC'));
    }

    public function testContains()
    {
        $element = new ElementDate(new DateTime('2013-09-01 23:55:55+02:00'));

        $this->assertTrue($element->contains('2013-09-01T21:55:55+00:00'));
        $this->assertFalse($element->contains('2013-06-15'));
    }

    public function test__toString()
    {
        $element = new ElementDate(new DateTime('2013-09-01 23:55:55+02:00'));

        $element->setFormat('c');
        $this->assertEquals('2013-09-01T23:55:55+02:00', (string) $element);
    }
}

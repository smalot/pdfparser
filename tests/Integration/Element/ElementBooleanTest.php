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

use Smalot\PdfParser\Element\ElementBoolean;
use Test\Smalot\PdfParser\TestCase;

class ElementBooleanTest extends TestCase
{
    public function testParse()
    {
        // Skipped.
        $offset = 0;
        $element = ElementBoolean::parse('ABC', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementBoolean::parse(' [ false ]', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementBoolean::parse(' << true >>', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementBoolean::parse(' / false ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementBoolean::parse(' 0 true ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementBoolean::parse(" 0 \n true ", null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        // Valid.
        $offset = 0;
        $element = ElementBoolean::parse(' true ', null, $offset);
        $this->assertTrue($element->getContent());
        $this->assertEquals(5, $offset);

        $offset = 0;
        $element = ElementBoolean::parse(' TRUE ', null, $offset);
        $this->assertTrue($element->getContent());
        $this->assertEquals(5, $offset);

        $offset = 0;
        $element = ElementBoolean::parse(' True', null, $offset);
        $this->assertTrue($element->getContent());
        $this->assertEquals(5, $offset);

        $offset = 0;
        $element = ElementBoolean::parse('true', null, $offset);
        $this->assertTrue($element->getContent());
        $this->assertEquals(4, $offset);

        $offset = 0;
        $element = ElementBoolean::parse('False', null, $offset);
        $this->assertFalse($element->getContent());
        $this->assertEquals(5, $offset);

        $offset = 0;
        $element = ElementBoolean::parse(" \n true ", null, $offset);
        $this->assertTrue($element->getContent());
        $this->assertEquals(7, $offset);
    }

    public function testGetContent()
    {
        $element = new ElementBoolean('true');
        $this->assertTrue($element->getContent());

        $element = new ElementBoolean('false');
        $this->assertFalse($element->getContent());
    }

    public function testEquals()
    {
        $element = new ElementBoolean('true');
        $this->assertTrue($element->equals(true));
        $this->assertFalse($element->equals(1));
        $this->assertFalse($element->equals(false));
        $this->assertFalse($element->equals(null));

        $element = new ElementBoolean('false');
        $this->assertTrue($element->equals(false));
        $this->assertFalse($element->equals(0));
        $this->assertFalse($element->equals(true));
        $this->assertFalse($element->equals(null));
    }

    public function testContains()
    {
        $element = new ElementBoolean('true');
        $this->assertTrue($element->contains(true));
        $this->assertFalse($element->contains(false));
        $this->assertFalse($element->contains(1));
    }

    public function test__toString()
    {
        $element = new ElementBoolean('true');
        $this->assertEquals('true', (string) $element);

        $element = new ElementBoolean('false');
        $this->assertEquals('false', (string) $element);
    }
}

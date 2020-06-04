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

use Smalot\PdfParser\Element\ElementNull;
use Test\Smalot\PdfParser\TestCase;

class ElementNullTest extends TestCase
{
    public function testParse()
    {
        // Skipped.
        $offset = 0;
        $element = ElementNull::parse('ABC', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementNull::parse(' [ null ]', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementNull::parse(' << null >>', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementNull::parse(' / null ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementNull::parse(' 0 null ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementNull::parse(" 0 \n null ", null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        // Valid.
        $offset = 0;
        $element = ElementNull::parse(' null ', null, $offset);
        $this->assertTrue(null === $element->getContent());
        $this->assertEquals(5, $offset);

        $offset = 0;
        $element = ElementNull::parse(' null ', null, $offset);
        $this->assertTrue(null === $element->getContent());
        $this->assertEquals(5, $offset);

        $offset = 0;
        $element = ElementNull::parse(' null', null, $offset);
        $this->assertTrue(null === $element->getContent());
        $this->assertEquals(5, $offset);

        $offset = 0;
        $element = ElementNull::parse('null', null, $offset);
        $this->assertTrue(null === $element->getContent());
        $this->assertEquals(4, $offset);

        $offset = 0;
        $element = ElementNull::parse(" \n null ", null, $offset);
        $this->assertTrue(null === $element->getContent());
        $this->assertEquals(7, $offset);
    }

    public function testGetContent()
    {
        $element = new ElementNull();
        $this->assertTrue(null === $element->getContent());
    }

    public function testEquals()
    {
        $element = new ElementNull();
        $this->assertTrue($element->equals(null));
        $this->assertFalse($element->equals(false));
        $this->assertFalse($element->equals(0));
        $this->assertFalse($element->equals(1));
    }

    public function testContains()
    {
        $element = new ElementNull();
        $this->assertTrue($element->contains(null));
        $this->assertFalse($element->contains(false));
        $this->assertFalse($element->contains(0));
    }

    public function test__toString()
    {
        $element = new ElementNull();
        $this->assertEquals('null', (string) $element);
    }
}

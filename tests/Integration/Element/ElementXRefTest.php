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

use Smalot\PdfParser\Element\ElementXRef;
use Test\Smalot\PdfParser\TestCase;

class ElementXRefTest extends TestCase
{
    public function testParse()
    {
        // Skipped.
        $offset = 0;
        $element = ElementXRef::parse('ABC', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementXRef::parse(' [ 5 0 R ]', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementXRef::parse(' << 5 0 R >>', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementXRef::parse(' / 5 0 R ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementXRef::parse(' 0 5 0 R ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementXRef::parse(" 0 \n 5 0 R ", null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        // Valid.
        $offset = 0;
        $element = ElementXRef::parse(' 5 0 R ', null, $offset);
        $this->assertEquals('5_0', $element->getContent());
        $this->assertEquals(6, $offset);

        $offset = 0;
        $element = ElementXRef::parse(' 5 0 R ', null, $offset);
        $this->assertEquals('5_0', $element->getContent());
        $this->assertEquals(6, $offset);

        $offset = 0;
        $element = ElementXRef::parse(' 5 0 R', null, $offset);
        $this->assertEquals('5_0', $element->getContent());
        $this->assertEquals(6, $offset);

        $offset = 0;
        $element = ElementXRef::parse('5 0 R', null, $offset);
        $this->assertEquals('5_0', $element->getContent());
        $this->assertEquals(5, $offset);

        $offset = 0;
        $element = ElementXRef::parse(" \n 5 0 R ", null, $offset);
        $this->assertEquals('5_0', $element->getContent());
        $this->assertEquals(8, $offset);
    }

    public function testGetContent()
    {
        $element = new ElementXRef('5_0');
        $this->assertEquals('5_0', $element->getContent());
    }

    public function testGetId()
    {
        $element = new ElementXRef('5_0');
        $this->assertEquals('5_0', $element->getId());
    }

    public function testEquals()
    {
        $element = new ElementXRef('5_0');
        $this->assertTrue($element->equals(5));
        $this->assertFalse($element->equals(8));
        $this->assertTrue($element->equals($element));
    }

    public function testContains()
    {
        $element = new ElementXRef('5_0');
        $this->assertTrue($element->contains(5));
        $this->assertFalse($element->contains(8));
        $this->assertTrue($element->contains($element));
    }

    public function test__toString()
    {
        $element = new ElementXRef('5_0');
        $this->assertEquals('#Obj#5_0', (string) $element);
    }
}

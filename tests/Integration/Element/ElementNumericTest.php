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

use Smalot\PdfParser\Element\ElementNumeric;
use Test\Smalot\PdfParser\TestCase;

class ElementNumericTest extends TestCase
{
    public function testParse()
    {
        // Skipped.
        $offset = 0;
        $element = ElementNumeric::parse('ABC', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementNumeric::parse(' [ 2 ]', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementNumeric::parse(' /2', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementNumeric::parse(" /2 \n 2", null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        // Valid.
        $offset = 0;
        $element = ElementNumeric::parse(' -2', null, $offset);
        $this->assertEquals(-2.0, $element->getContent());
        $this->assertEquals(3, $offset);

        $offset = 0;
        $element = ElementNumeric::parse('2BC', null, $offset);
        $this->assertEquals(2.0, $element->getContent());
        $this->assertEquals(1, $offset);

        $offset = 0;
        $element = ElementNumeric::parse(' 2BC', null, $offset);
        $this->assertEquals(2.0, $element->getContent());
        $this->assertEquals(2, $offset);

        $offset = 0;
        $element = ElementNumeric::parse(' -2BC', null, $offset);
        $this->assertEquals(-2.0, $element->getContent());
        $this->assertEquals(3, $offset);

        $offset = 0;
        $element = ElementNumeric::parse(' -2', null, $offset);
        $this->assertEquals(-2.0, $element->getContent());
        $this->assertEquals(3, $offset);

        $offset = 0;
        $element = ElementNumeric::parse(' 2 0 obj', null, $offset);
        $this->assertEquals(2.0, $element->getContent());
        $this->assertEquals(2, $offset);

        $offset = 0;
        $element = ElementNumeric::parse(" \n -2 ", null, $offset);
        $this->assertEquals(-2.0, $element->getContent());
        $this->assertEquals(5, $offset);
    }

    public function testGetContent()
    {
        $element = new ElementNumeric('B');
        $this->assertEquals(0.0, $element->getContent());

        $element = new ElementNumeric('-2.5');
        $this->assertEquals(-2.5, $element->getContent());

        $element = new ElementNumeric('-2');
        $this->assertEquals(-2.0, $element->getContent());

        $element = new ElementNumeric(' -2');
        $this->assertEquals(-2.0, $element->getContent());

        $element = new ElementNumeric('2.5');
        $this->assertEquals(2.5, $element->getContent());

        $element = new ElementNumeric('2');
        $this->assertEquals(2.0, $element->getContent());
    }

    public function testEquals()
    {
        $element = new ElementNumeric('1');
        $this->assertFalse($element->equals('B'));
        $element = new ElementNumeric('1.5');
        $this->assertFalse($element->equals('B'));

        $element = new ElementNumeric('2');
        $this->assertTrue($element->equals('2'));
        $element = new ElementNumeric('2');
        $this->assertFalse($element->equals('3'));

        $element = new ElementNumeric('-2');
        $this->assertTrue($element->equals('-2'));
        $element = new ElementNumeric('-2');
        $this->assertFalse($element->equals('-3'));

        $element = new ElementNumeric('2.5');
        $this->assertTrue($element->equals('2.5'));
        $element = new ElementNumeric('2.5');
        $this->assertFalse($element->equals('3.5'));

        $element = new ElementNumeric('-2.5');
        $this->assertTrue($element->equals('-2.5'));
        $element = new ElementNumeric('-2.5');
        $this->assertFalse($element->equals('-3.5'));
    }

    public function testContains()
    {
        $element = new ElementNumeric('1');
        $this->assertFalse($element->contains('B'));
        $element = new ElementNumeric('1.5');
        $this->assertFalse($element->contains('B'));

        $element = new ElementNumeric('2');
        $this->assertTrue($element->contains('2'));
        $element = new ElementNumeric('2');
        $this->assertFalse($element->contains('3'));

        $element = new ElementNumeric('-2');
        $this->assertTrue($element->contains('-2'));
        $element = new ElementNumeric('-2');
        $this->assertFalse($element->contains('-3'));

        $element = new ElementNumeric('2.5');
        $this->assertTrue($element->contains('2.5'));
        $element = new ElementNumeric('2.5');
        $this->assertFalse($element->contains('3.5'));

        $element = new ElementNumeric('-2.5');
        $this->assertTrue($element->contains('-2.5'));
        $element = new ElementNumeric('-2.5');
        $this->assertFalse($element->contains('-3.5'));
    }

    public function test__toString()
    {
        $element = new ElementNumeric('B');
        $this->assertEquals('0', (string) $element);
        $element = new ElementNumeric('1B');
        $this->assertEquals('1', (string) $element);

        $element = new ElementNumeric('2');
        $this->assertEquals('2', (string) $element);

        $element = new ElementNumeric('-2');
        $this->assertEquals('-2', (string) $element);

        $element = new ElementNumeric('2.5');
        $this->assertEquals('2.5', (string) $element);

        $element = new ElementNumeric('-2.5');
        $this->assertEquals('-2.5', (string) $element);
    }
}

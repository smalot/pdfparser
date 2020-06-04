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

use Smalot\PdfParser\Element\ElementName;
use Test\Smalot\PdfParser\TestCase;

class ElementNameTest extends TestCase
{
    public function testParse()
    {
        // Skipped.
        $offset = 0;
        $element = ElementName::parse('ABC', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);
        $offset = 0;
        $element = ElementName::parse(' [ /ABC 5 6 ]', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);
        $offset = 0;
        $element = ElementName::parse(' << invalid >>', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);
        $offset = 0;
        $element = ElementName::parse(' / FlateDecode ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);
        $offset = 0;
        $element = ElementName::parse(' 0 /FlateDecode ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);
        $offset = 0;
        $element = ElementName::parse(" 0 \n /FlateDecode ", null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        // Valid.
        $offset = 0;
        $element = ElementName::parse(' /FlateDecode ', null, $offset);
        $this->assertEquals('FlateDecode', $element->getContent());
        $this->assertEquals(13, $offset);

        $offset = 0;
        $element = ElementName::parse(' /FlateDecode', null, $offset);
        $this->assertEquals('FlateDecode', $element->getContent());
        $this->assertEquals(13, $offset);

        $offset = 0;
        $element = ElementName::parse('/FlateDecode', null, $offset);
        $this->assertEquals('FlateDecode', $element->getContent());
        $this->assertEquals(12, $offset);

        $offset = 0;
        $element = ElementName::parse(" \n /FlateDecode ", null, $offset);
        $this->assertEquals('FlateDecode', $element->getContent());
        $this->assertEquals(15, $offset);

        $offset = 0;
        $element = ElementName::parse('/FlateDecode2', null, $offset);
        $this->assertEquals('FlateDecode2', $element->getContent());
        $this->assertEquals(13, $offset);

        $offset = 0;
        $element = ElementName::parse('/Flate-Decode2', null, $offset);
        $this->assertEquals('Flate-Decode2', $element->getContent());
        $this->assertEquals(14, $offset);

        $offset = 0;
        $element = ElementName::parse('/OJHCYD+Cambria', null, $offset);
        $this->assertEquals('OJHCYD+Cambria', $element->getContent());
        $this->assertEquals(15, $offset);

        $offset = 0;
        $element = ElementName::parse('/OJHCYD+Cambria,Bold', null, $offset);
        $this->assertEquals('OJHCYD+Cambria,Bold', $element->getContent());
        $this->assertEquals(20, $offset);

        $offset = 0;
        $element = ElementName::parse('/Flate_Decode2', null, $offset);
        $this->assertEquals('Flate', $element->getContent());
        $this->assertEquals(6, $offset);

        $offset = 0;
        $element = ElementName::parse('/Flate.Decode2', null, $offset);
        $this->assertEquals('Flate.Decode2', $element->getContent());
        $this->assertEquals(14, $offset);
    }

    public function testGetContent()
    {
        $element = new ElementName('FlateDecode');
        $this->assertEquals('FlateDecode', $element->getContent());
    }

    public function testEquals()
    {
        $element = new ElementName('FlateDecode');
        $this->assertTrue($element->equals('FlateDecode'));
        $this->assertFalse($element->equals('Flatedecode'));

        $element = new ElementName('FlateDecode2');
        $this->assertTrue($element->equals('FlateDecode2'));
        $this->assertFalse($element->equals('FlateDecode3'));

        $element = new ElementName('Flate-Decode2');
        $this->assertTrue($element->equals('Flate-Decode2'));
        $this->assertFalse($element->equals('Flate-Decode3'));
    }

    public function testContains()
    {
        $element = new ElementName('FlateDecode');
        $this->assertTrue($element->contains('FlateDecode'));
        $this->assertFalse($element->contains('Flatedecode'));

        $element = new ElementName('FlateDecode2');
        $this->assertTrue($element->contains('FlateDecode2'));
        $this->assertFalse($element->contains('FlateDecode3'));

        $element = new ElementName('Flate-Decode2');
        $this->assertTrue($element->contains('Flate-Decode2'));
        $this->assertFalse($element->contains('Flate-Decode3'));
    }

    public function test__toString()
    {
        $element = new ElementName('FlateDecode');
        $this->assertEquals('FlateDecode', (string) $element);
    }
}

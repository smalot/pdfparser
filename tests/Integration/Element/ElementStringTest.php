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

use Smalot\PdfParser\Element\ElementString;
use Test\Smalot\PdfParser\TestCase;

class ElementStringTest extends TestCase
{
    public function testParse()
    {
        // Skipped.
        $offset = 0;
        $element = ElementString::parse('ABC', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementString::parse(' [ (ABC) 5 6 ]', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementString::parse(' << (invalid) >>', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementString::parse(' / (FlateDecode) ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementString::parse(' 0 (FlateDecode) ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementString::parse(" 0 \n (FlateDecode) ", null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        // Valid.
        $offset = 0;
        $element = ElementString::parse(' (Copyright) ', null, $offset);
        $this->assertEquals('Copyright', $element->getContent());
        $this->assertEquals(12, $offset);

        $offset = 0;
        $element = ElementString::parse(' (Copyright) ', null, $offset);
        $this->assertEquals('Copyright', $element->getContent());
        $this->assertEquals(12, $offset);

        $offset = 0;
        $element = ElementString::parse(' (Copyright)', null, $offset);
        $this->assertEquals('Copyright', $element->getContent());
        $this->assertEquals(12, $offset);

        $offset = 0;
        $element = ElementString::parse('(Copyright)', null, $offset);
        $this->assertEquals('Copyright', $element->getContent());
        $this->assertEquals(11, $offset);

        $offset = 0;
        $element = ElementString::parse('(Copy-right2)', null, $offset);
        $this->assertEquals('Copy-right2', $element->getContent());
        $this->assertEquals(13, $offset);

        $offset = 0;
        $element = ElementString::parse(" \n (Copyright) ", null, $offset);
        $this->assertEquals('Copyright', $element->getContent());
        $this->assertEquals(14, $offset);

        $offset = 0;
        $element = ElementString::parse('()', null, $offset);
        $this->assertEquals('', $element->getContent());
        $this->assertEquals(2, $offset);

        /*
         * Complex study case : Unicode + octal.
         */
        $offset = 0;
        $element = ElementString::parse('(ABC\\))', null, $offset);
        $this->assertEquals('ABC)', $element->getContent());
        $this->assertEquals(7, $offset);

        $offset = 0;
        $element = ElementString::parse("(\xFE\xFF\\000M)", null, $offset);
        $this->assertEquals('M', $element->getContent());
        $this->assertEquals(9, $offset);

        $offset = 0;
        $element = ElementString::parse('(<20>)', null, $offset);
        $this->assertEquals(' ', $element->getContent());
        $this->assertEquals(6, $offset);

        $offset = 0;
        $element = ElementString::parse('(Gutter\\ console\\ assembly)', null, $offset);
        $this->assertEquals('Gutter console assembly', $element->getContent());
        $this->assertEquals(27, $offset);
    }

    public function testGetContent()
    {
        $element = new ElementString('Copyright');
        $this->assertEquals('Copyright', $element->getContent());
    }

    public function testEquals()
    {
        $element = new ElementString('CopyRight');
        $this->assertTrue($element->equals('CopyRight'));
        $this->assertFalse($element->equals('Flatedecode'));

        $element = new ElementString('CopyRight2');
        $this->assertTrue($element->equals('CopyRight2'));
        $this->assertFalse($element->equals('CopyRight3'));

        $element = new ElementString('Flate-Decode2');
        $this->assertTrue($element->equals('Flate-Decode2'));
        $this->assertFalse($element->equals('Flate-Decode3'));
    }

    public function testContains()
    {
        $element = new ElementString('CopyRight');
        $this->assertTrue($element->contains('CopyRight'));
        $this->assertFalse($element->contains('Copyright'));

        $element = new ElementString('CopyRight2');
        $this->assertTrue($element->contains('CopyRight2'));
        $this->assertFalse($element->contains('CopyRight3'));
    }

    public function test__toString()
    {
        $element = new ElementString('CopyRight');
        $this->assertEquals('CopyRight', (string) $element);
    }
}

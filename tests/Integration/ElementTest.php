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

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementBoolean;
use Smalot\PdfParser\Element\ElementDate;
use Smalot\PdfParser\Element\ElementName;
use Smalot\PdfParser\Element\ElementNull;
use Smalot\PdfParser\Element\ElementNumeric;
use Smalot\PdfParser\Element\ElementString;
use Smalot\PdfParser\Element\ElementXRef;
use Smalot\PdfParser\Header;
use Test\Smalot\PdfParser\TestCase;

class ElementTest extends TestCase
{
    public function testParse()
    {
        $document = $this->getDocumentInstance();

        // Only_values = false.
        $content = '/NameType /FlateDecode
        /Contents[4 0 R 42]/Fonts<</F1 41/F2 43>>/NullType
        null/StringType(hello)/DateType(D:20130901235555+02\'00\')/XRefType 2 0 R
        /NumericType 8/HexaType<0020>/BooleanType false';
        $offset = 0;

        $elements = Element::parse($content, $document, $offset, false);

        $this->assertTrue(\array_key_exists('NameType', $elements));
        $this->assertTrue($elements['NameType'] instanceof ElementName);
        $this->assertEquals('FlateDecode', $elements['NameType']->getContent());

        $this->assertTrue(\array_key_exists('Contents', $elements));
        $this->assertTrue($elements['Contents'] instanceof ElementArray);
        $this->assertTrue($elements['Contents']->contains(42));

        $this->assertTrue(\array_key_exists('Fonts', $elements));
        $this->assertTrue($elements['Fonts'] instanceof Header);

        $this->assertTrue(\array_key_exists('NullType', $elements));
        $this->assertTrue($elements['NullType'] instanceof ElementNull);
        $this->assertEquals('null', (string) $elements['NullType']);

        $this->assertTrue(\array_key_exists('StringType', $elements));
        $this->assertTrue($elements['StringType'] instanceof ElementString);
        $this->assertEquals('hello', $elements['StringType']->getContent());

        $this->assertTrue(\array_key_exists('DateType', $elements));
        $this->assertTrue($elements['DateType'] instanceof ElementDate);

        $this->assertTrue(\array_key_exists('XRefType', $elements));
        $this->assertTrue($elements['XRefType'] instanceof ElementXRef);
        $this->assertEquals('2_0', $elements['XRefType']->getId());

        $this->assertTrue(\array_key_exists('NumericType', $elements));
        $this->assertTrue($elements['NumericType'] instanceof ElementNumeric);
        $this->assertEquals('8', (string) $elements['NumericType']);

        $this->assertTrue(\array_key_exists('HexaType', $elements));
        $this->assertTrue($elements['HexaType'] instanceof ElementString);
        $this->assertEquals(' ', (string) $elements['HexaType']);

        $this->assertTrue(\array_key_exists('BooleanType', $elements));
        $this->assertTrue($elements['BooleanType'] instanceof ElementBoolean);
        $this->assertFalse($elements['BooleanType']->getContent());

        // Only_values = true.
        $content = '/NameType /FlateDecode';
        $offset = 0;
        $elements = Element::parse($content, $document, $offset, true);
        $this->assertEquals(2, \count($elements));
        $this->assertEquals(22, $offset);

        // Test error.
        $content = '/NameType /FlateDecode $$$';
        $offset = 0;
        $elements = Element::parse($content, $document, $offset, false);
        $this->assertEquals(1, \count($elements));
        $this->assertEquals(22, $offset);
        $this->assertEquals('NameType', key($elements));
        $this->assertTrue(current($elements) instanceof ElementName);

        $content = '/NameType $$$';
        $offset = 0;
        $elements = Element::parse($content, $document, $offset, false);
        $this->assertEquals(0, $offset);
        $this->assertEquals(0, \count($elements));
    }

    public function testGetContent()
    {
        $element = $this->getElementInstance(42);
        $content = $element->getContent();
        $this->assertEquals(42, $content);

        $element = $this->getElementInstance([4, 2]);
        $this->assertEquals(2, \count($element->getContent()));
    }

    public function testEquals()
    {
        $element = $this->getElementInstance(2);

        $this->assertTrue($element->equals(2));
    }

    public function testContains()
    {
        $element = $this->getElementInstance([$this->getElementInstance(4), $this->getElementInstance(2)]);

        $this->assertTrue($element->contains(2));
        $this->assertFalse($element->contains(8));
    }

    public function test__toString()
    {
        $this->assertEquals((string) $this->getElementInstance('2'), '2');
    }
}

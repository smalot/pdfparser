<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
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
 *
 */

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Element
 *
 * @package Smalot\PdfParser\Tests\Units
 */
class Element extends atoum\test
{
    public function testParse()
    {
        $document = new \Smalot\PdfParser\Document(array());

        // Only_values = false.
        $content  = '/NameType /FlateDecode
        /Contents[4 0 R 42]/Fonts<</F1 41/F2 43>>/NullType
        null/StringType(hello)/DateType(D:20130901235555+02\'00\')/XRefType 2 0 R
        /NumericType 8/HexaType<0020>/BooleanType false';
        $offset   = 0;
        $elements = \Smalot\PdfParser\Element::parse($content, $document, $offset, false);

        $this->assert->array($elements)->hasKey('NameType');
        $this->assert->object($elements['NameType'])->isInstanceOf('\Smalot\PdfParser\Element\ElementName');
        $this->assert->string($elements['NameType']->getContent())->isEqualTo('FlateDecode');

        $this->assert->boolean(array_key_exists('Contents', $elements))->isEqualTo(true);
        $this->assert->object($elements['Contents'])->isInstanceOf('\Smalot\PdfParser\Element\ElementArray');
        $this->assert->boolean($elements['Contents']->contains(42))->isEqualTo(true);

        $this->assert->boolean(array_key_exists('Fonts', $elements))->isEqualTo(true);
        $this->assert->object($elements['Fonts'])->isInstanceOf('\Smalot\PdfParser\Header');

        $this->assert->boolean(array_key_exists('NullType', $elements))->isEqualTo(true);
        $this->assert->object($elements['NullType'])->isInstanceOf('\Smalot\PdfParser\Element\ElementNull');
        $this->assert->castToString($elements['NullType'])->isEqualTo('null');

        $this->assert->boolean(array_key_exists('StringType', $elements))->isEqualTo(true);
        $this->assert->object($elements['StringType'])->isInstanceOf('\Smalot\PdfParser\Element\ElementString');
        $this->assert->string($elements['StringType']->getContent())->isEqualTo('hello');

        $this->assert->boolean(array_key_exists('DateType', $elements))->isEqualTo(true);
        $this->assert->object($elements['DateType'])->isInstanceOf('\Smalot\PdfParser\Element\ElementDate');
//        $this->assert->castToString($elements['DateType'])->isEqualTo('2013-09-01T23:55:55+02:00');

        $this->assert->boolean(array_key_exists('XRefType', $elements))->isEqualTo(true);
        $this->assert->object($elements['XRefType'])->isInstanceOf('\Smalot\PdfParser\Element\ElementXRef');
        $this->assert->string($elements['XRefType']->getId())->isEqualTo('2_0');

        $this->assert->boolean(array_key_exists('NumericType', $elements))->isEqualTo(true);
        $this->assert->object($elements['NumericType'])->isInstanceOf('\Smalot\PdfParser\Element\ElementNumeric');
        $this->assert->castToString($elements['NumericType'])->isEqualTo('8');

        $this->assert->boolean(array_key_exists('HexaType', $elements))->isEqualTo(true);
        $this->assert->object($elements['HexaType'])->isInstanceOf('\Smalot\PdfParser\Element\ElementString');
        $this->assert->string($elements['HexaType']->getContent())->isEqualTo(' ');

        $this->assert->boolean(array_key_exists('BooleanType', $elements))->isEqualTo(true);
        $this->assert->object($elements['BooleanType'])->isInstanceOf('\Smalot\PdfParser\Element\ElementBoolean');
        $this->assert->boolean($elements['BooleanType']->getContent())->isEqualTo(false);

        // Only_values = true.
        $content  = '/NameType /FlateDecode';
        $offset   = 0;
        $elements = \Smalot\PdfParser\Element::parse($content, $document, $offset, true);
        $this->assert->array($elements)->hasSize(2);
        $this->assert->integer($offset)->isEqualTo(22);

        // Test error.
        $content  = '/NameType /FlateDecode $$$';
        $offset   = 0;
        $elements = \Smalot\PdfParser\Element::parse($content, $document, $offset, false);
        $this->assert->array($elements)->hasSize(1);
        $this->assert->integer($offset)->isEqualTo(22);
        $this->assert->string(key($elements))->isEqualTo('NameType');
        $this->assert->object(current($elements))->isInstanceOf('\Smalot\PdfParser\Element\ElementName');

        $content  = '/NameType $$$';
        $offset   = 0;
        $elements = \Smalot\PdfParser\Element::parse($content, $document, $offset, false);
        $this->assert->integer($offset)->isEqualTo(0);
        $this->assert->array($elements)->isEmpty();

        /*$this->assert->boolean(array_key_exists('NameType', $elements))->isEqualTo(true);
        $this->assert->boolean($elements['NameType'])->isInstanceOf('\Smalot\PdfParser\Element\ElementName)->isEqualTo(true);
        $this->assert->string($elements['NameType']->getContent())->isEqualTo('FlateDecode');*/
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element(42);
        $content = $element->getContent();
        $this->assert->integer($content)->isEqualTo(42);

        $element = new \Smalot\PdfParser\Element(array(4, 2));
        $content = $element->getContent();
        $this->assert->array($content)->hasSize(2);
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element(2);

        $this->assert->boolean($element->equals(2))->isEqualTo(true);
        $this->assert->boolean($element->equals(8))->isEqualTo(false);
    }

    public function testContains()
    {
        $val_4   = new \Smalot\PdfParser\Element(4);
        $val_2   = new \Smalot\PdfParser\Element(2);
        $element = new \Smalot\PdfParser\Element(array($val_4, $val_2));

        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element(2);
        $this->assert->castToString($element)->isEqualTo('2');
    }
}

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

namespace Smalot\PdfParser\Tests\Units\Element;

use mageekguy\atoum;

/**
 * Class ElementName
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementName extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('ABC', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' [ /ABC 5 6 ]', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' << invalid >>', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' / FlateDecode ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' 0 /FlateDecode ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(" 0 \n /FlateDecode ", null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' /FlateDecode ', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('FlateDecode');
        $this->assert->integer($offset)->isEqualTo(13);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' /FlateDecode', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('FlateDecode');
        $this->assert->integer($offset)->isEqualTo(13);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/FlateDecode', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('FlateDecode');
        $this->assert->integer($offset)->isEqualTo(12);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(" \n /FlateDecode ", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('FlateDecode');
        $this->assert->integer($offset)->isEqualTo(15);

        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/FlateDecode2', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('FlateDecode2');
        $this->assert->integer($offset)->isEqualTo(13);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/Flate-Decode2', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Flate-Decode2');
        $this->assert->integer($offset)->isEqualTo(14);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/OJHCYD+Cambria', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('OJHCYD+Cambria');
        $this->assert->integer($offset)->isEqualTo(15);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/OJHCYD+Cambria,Bold', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('OJHCYD+Cambria,Bold');
        $this->assert->integer($offset)->isEqualTo(20);

        //
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/Flate_Decode2', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Flate');
        $this->assert->integer($offset)->isEqualTo(6);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/Flate.Decode2', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Flate.Decode2');
        $this->assert->integer($offset)->isEqualTo(14);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode');
        $this->assert->string($element->getContent())->isEqualTo('FlateDecode');
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode');
        $this->assert->boolean($element->equals('FlateDecode'))->isEqualTo(true);
        $this->assert->boolean($element->equals('Flatedecode'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode2');
        $this->assert->boolean($element->equals('FlateDecode2'))->isEqualTo(true);
        $this->assert->boolean($element->equals('FlateDecode3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementName('Flate-Decode2');
        $this->assert->boolean($element->equals('Flate-Decode2'))->isEqualTo(true);
        $this->assert->boolean($element->equals('Flate-Decode3'))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode');
        $this->assert->boolean($element->contains('FlateDecode'))->isEqualTo(true);
        $this->assert->boolean($element->contains('Flatedecode'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode2');
        $this->assert->boolean($element->contains('FlateDecode2'))->isEqualTo(true);
        $this->assert->boolean($element->contains('FlateDecode3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementName('Flate-Decode2');
        $this->assert->boolean($element->contains('Flate-Decode2'))->isEqualTo(true);
        $this->assert->boolean($element->contains('Flate-Decode3'))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode');
        $this->assert->castToString($element)->isEqualTo('FlateDecode');
    }
}

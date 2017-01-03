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
 * Class ElementXRef
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementXRef extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse('ABC', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' [ 5 0 R ]', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' << 5 0 R >>', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' / 5 0 R ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' 0 5 0 R ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(" 0 \n 5 0 R ", null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' 5 0 R ', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('5_0');
        $this->assert->integer($offset)->isEqualTo(6);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' 5 0 R ', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('5_0');
        $this->assert->integer($offset)->isEqualTo(6);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' 5 0 R', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('5_0');
        $this->assert->integer($offset)->isEqualTo(6);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse('5 0 R', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('5_0');
        $this->assert->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(" \n 5 0 R ", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('5_0');
        $this->assert->integer($offset)->isEqualTo(8);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementXRef('5_0');
        $this->assert->string($element->getContent())->isEqualTo('5_0');
    }

    public function testGetId()
    {
        $element = new \Smalot\PdfParser\Element\ElementXRef('5_0');
        $this->assert->string($element->getId())->isEqualTo('5_0');
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementXRef('5_0');
        $this->assert->boolean($element->equals(5))->isEqualTo(true);
        $this->assert->boolean($element->equals(8))->isEqualTo(false);
        $this->assert->boolean($element->equals($element))->isEqualTo(true);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementXRef('5_0');
        $this->assert->boolean($element->contains(5))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
        $this->assert->boolean($element->contains($element))->isEqualTo(true);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementXRef('5_0');
        $this->assert->castToString($element)->isEqualTo('#Obj#5_0');
    }
}

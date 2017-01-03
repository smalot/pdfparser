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
 * Class ElementNumeric
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementNumeric extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse('ABC', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' [ 2 ]', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' /2', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(" /2 \n 2", null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' -2', null, $offset);
        $this->assert->float($element->getContent())->isEqualTo(-2.0);
        $this->assert->integer($offset)->isEqualTo(3);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse('2BC', null, $offset);
        $this->assert->float($element->getContent())->isEqualTo(2.0);
        $this->assert->integer($offset)->isEqualTo(1);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' 2BC', null, $offset);
        $this->assert->float($element->getContent())->isEqualTo(2.0);
        $this->assert->integer($offset)->isEqualTo(2);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' -2BC', null, $offset);
        $this->assert->float($element->getContent())->isEqualTo(-2.0);
        $this->assert->integer($offset)->isEqualTo(3);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' -2', null, $offset);
        $this->assert->float($element->getContent())->isEqualTo(-2.0);
        $this->assert->integer($offset)->isEqualTo(3);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' 2 0 obj', null, $offset);
        $this->assert->float($element->getContent())->isEqualTo(2.0);
        $this->assert->integer($offset)->isEqualTo(2);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(" \n -2 ", null, $offset);
        $this->assert->float($element->getContent())->isEqualTo(-2.0);
        $this->assert->integer($offset)->isEqualTo(5);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementNumeric('B');
        $this->assert->float($element->getContent())->isEqualTo(0.0);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->assert->float($element->getContent())->isEqualTo(-2.5);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->assert->float($element->getContent())->isEqualTo(-2.0);
        $element = new \Smalot\PdfParser\Element\ElementNumeric(' -2');
        $this->assert->float($element->getContent())->isEqualTo(-2.0);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->assert->float($element->getContent())->isEqualTo(2.5);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->assert->float($element->getContent())->isEqualTo(2.0);
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementNumeric('1');
        $this->assert->boolean($element->equals('B'))->isEqualTo(false);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('1.5');
        $this->assert->boolean($element->equals('B'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->assert->boolean($element->equals('2'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->assert->boolean($element->equals('3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->assert->boolean($element->equals('-2'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->assert->boolean($element->equals('-3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->assert->boolean($element->equals('2.5'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->assert->boolean($element->equals('3.5'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->assert->boolean($element->equals('-2.5'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->assert->boolean($element->equals('-3.5'))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementNumeric('1');
        $this->assert->boolean($element->contains('B'))->isEqualTo(false);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('1.5');
        $this->assert->boolean($element->contains('B'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->assert->boolean($element->contains('2'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->assert->boolean($element->contains('3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->assert->boolean($element->contains('-2'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->assert->boolean($element->contains('-3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->assert->boolean($element->contains('2.5'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->assert->boolean($element->contains('3.5'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->assert->boolean($element->contains('-2.5'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->assert->boolean($element->contains('-3.5'))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementNumeric('B');
        $this->assert->castToString($element)->isEqualTo('0');
        $element = new \Smalot\PdfParser\Element\ElementNumeric('1B');
        $this->assert->castToString($element)->isEqualTo('1');

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->assert->castToString($element)->isEqualTo('2');

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->assert->castToString($element)->isEqualTo('-2');

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->assert->castToString($element)->isEqualTo('2.5');

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->assert->castToString($element)->isEqualTo('-2.5');
    }
}

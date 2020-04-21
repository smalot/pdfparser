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
 * Class ElementDate
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementDate extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse('ABC', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' [ (ABC) 5 6 ]', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' << (invalid) >>', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' / (FlateDecode) ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' 0 (FlateDecode) ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(" 0 \n (FlateDecode) ", null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' (D:20130901235555+02\'00\') ', null, $offset);
        $element->setFormat('c');
        $this->assert->object($element->getContent())->isInstanceOf('\DateTime');
        $this->assert->castToString($element)->isEqualTo('2013-09-01T23:55:55+02:00');
        $this->assert->integer($offset)->isEqualTo(26);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' (D:20130901235555+02\'00\') ', null, $offset);
        $element->setFormat('c');
        $this->assert->object($element->getContent())->isInstanceOf('\DateTime');
        $this->assert->castToString($element)->isEqualTo('2013-09-01T23:55:55+02:00');
        $this->assert->integer($offset)->isEqualTo(26);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' (D:20130901235555+02\'00\')', null, $offset);
        $element->setFormat('c');
        $this->assert->object($element->getContent())->isInstanceOf('\DateTime');
        $this->assert->castToString($element)->isEqualTo('2013-09-01T23:55:55+02:00');
        $this->assert->integer($offset)->isEqualTo(26);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse('(D:20130901235555+02\'00\')', null, $offset);
        $element->setFormat('c');
        $this->assert->object($element->getContent())->isInstanceOf('\DateTime');
        $this->assert->castToString($element)->isEqualTo('2013-09-01T23:55:55+02:00');
        $this->assert->integer($offset)->isEqualTo(25);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(" \n (D:20130901235555+02'00') ", null, $offset);
        $element->setFormat('c');
        $this->assert->object($element->getContent())->isInstanceOf('\DateTime');
        $this->assert->castToString($element)->isEqualTo('2013-09-01T23:55:55+02:00');
        $this->assert->integer($offset)->isEqualTo(28);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(" \n (D:20130901235555) ", null, $offset);
        $element->setFormat('c');
        $this->assert->object($element->getContent())->isInstanceOf('\DateTime');
        $this->assert->boolean($element->equals(new \DateTime('2013-09-01T23:55:55+00:00')))->isEqualTo(true);
        $this->assert->integer($offset)->isEqualTo(21);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse("(D:20131206091846Z00'00')", null, $offset);
        $element->setFormat('c');
        $this->assert->object($element->getContent())->isInstanceOf('\DateTime');
        //$this->assert->boolean($element->equals(new \DateTime('2013-09-01T23:55:55')))->isEqualTo(true);
        $this->assert->integer($offset)->isEqualTo(25);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(" \n (D:1-23-2014, 19:02:15-03'00') ", null, $offset);
        $element->setFormat('c');
        $this->assert->object($element->getContent())->isInstanceOf('\DateTime');
        $this->assert->castToString($element)->isEqualTo('2014-01-23T19:02:15-03:00');
        $this->assert->integer($offset)->isEqualTo(33);

        // Format invalid
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(" \n (D:2013+02'00') ", null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementDate(new \DateTime('2013-09-01 23:55:55+02:00'));
        $this->assert->dateTime($element->getContent())->isEqualTo(new \DateTime('2013-09-01 21:55:55+00:00'));

        try {
            $element = new \Smalot\PdfParser\Element\ElementDate('2013-09-01 23:55:55+02:00');
            $this->assert->boolean(false)->isEqualTo(true);
        } catch (\Exception $e) {
            $this->assert->exception($e)->hasMessage('DateTime required.');
        }
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementDate(new \DateTime('2013-09-01 23:55:55+02:00'));
        $element->setFormat('c');
        $this->assert->boolean($element->equals('2013-09-01T23:55:55+02:00'))->isEqualTo(true);
        $this->assert->boolean($element->equals('2013-09-01T23:55:55+01:00'))->isEqualTo(false);
        $this->assert->boolean($element->equals(new \DateTime('2013-09-01T21:55:55+00:00')))->isEqualTo(true);
        $this->assert->boolean($element->equals(new \DateTime('2013-09-01T23:55:55+01:00')))->isEqualTo(false);
        $this->assert->boolean($element->equals('ABC'))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementDate(new \DateTime('2013-09-01 23:55:55+02:00'));
        $this->assert->boolean($element->contains('2013-09-01T21:55:55+00:00'))->isEqualTo(true);
        $this->assert->boolean($element->contains('2013-06-15'))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementDate(new \DateTime('2013-09-01 23:55:55+02:00'));
        $element->setFormat('c');
        $this->assert->castToString($element)->isEqualTo('2013-09-01T23:55:55+02:00');
    }
}

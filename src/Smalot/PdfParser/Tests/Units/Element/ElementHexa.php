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
 * Class ElementHexa
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementHexa extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse('ABC', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(' [ <0020> 5 6 ]', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(' << <0020> >>', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(' / <0020> ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(' 0 <0020> ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(" 0 \n <0020> ", null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(' <0020> ', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo(' ');
        $this->assert->integer($offset)->isEqualTo(7);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(' <0020> ', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo(' ');
        $this->assert->integer($offset)->isEqualTo(7);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(' <0020>', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo(' ');
        $this->assert->integer($offset)->isEqualTo(7);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse('<0020>', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo(' ');
        $this->assert->integer($offset)->isEqualTo(6);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(" \n <0020> ", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo(' ');
        $this->assert->integer($offset)->isEqualTo(9);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(" \n <5465616d204d616e6167656d656e742053797374656d73> ", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Team Management Systems');
        $this->assert->integer($offset)->isEqualTo(51);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(" \n <5265706f72744275696c646572> ", null, $offset);
        $this->assert->object($element)->isInstanceOf('\Smalot\PdfParser\Element\ElementString');
        $this->assert->string($element->getContent())->isEqualTo('ReportBuilder');
        $this->assert->integer($offset)->isEqualTo(31);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementHexa::parse(" \n <443a3230313331323137313334303435303027303027> ", null, $offset);
        $this->assert->object($element)->isInstanceOf('\Smalot\PdfParser\Element\ElementDate');
        $this->assert->castToString($element)->isEqualTo('2013-12-17T13:40:45+00:00');
        $this->assert->integer($offset)->isEqualTo(49);
    }
}

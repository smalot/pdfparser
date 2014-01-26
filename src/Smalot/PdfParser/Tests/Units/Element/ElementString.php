<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-3.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2014 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 *
 */

namespace Smalot\PdfParser\Tests\Units\Element;

use mageekguy\atoum;

/**
 * Class ElementString
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementString extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse('ABC', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' [ (ABC) 5 6 ]', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' << (invalid) >>', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' / (FlateDecode) ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' 0 (FlateDecode) ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(" 0 \n (FlateDecode) ", null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' (Copyright) ', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
        $this->assert->integer($offset)->isEqualTo(12);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' (Copyright) ', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
        $this->assert->integer($offset)->isEqualTo(12);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' (Copyright)', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
        $this->assert->integer($offset)->isEqualTo(12);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse('(Copyright)', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
        $this->assert->integer($offset)->isEqualTo(11);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse('(Copy-right2)', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copy-right2');
        $this->assert->integer($offset)->isEqualTo(13);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(" \n (Copyright) ", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
        $this->assert->integer($offset)->isEqualTo(14);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse('()', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('');
        $this->assert->integer($offset)->isEqualTo(2);

        // Complex study case : Unicode + octal.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse("(ABC\\))", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('ABC)');
        $this->assert->integer($offset)->isEqualTo(7);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse("(\xFE\xFF\\000M)", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('M');
        $this->assert->integer($offset)->isEqualTo(9);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse("(<20>)", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo(' ');
        $this->assert->integer($offset)->isEqualTo(6);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse("(Gutter\\ console\\ assembly)", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Gutter console assembly');
        $this->assert->integer($offset)->isEqualTo(27);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementString('Copyright');
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementString('CopyRight');
        $this->assert->boolean($element->equals('CopyRight'))->isEqualTo(true);
        $this->assert->boolean($element->equals('Flatedecode'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementString('CopyRight2');
        $this->assert->boolean($element->equals('CopyRight2'))->isEqualTo(true);
        $this->assert->boolean($element->equals('CopyRight3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementString('Flate-Decode2');
        $this->assert->boolean($element->equals('Flate-Decode2'))->isEqualTo(true);
        $this->assert->boolean($element->equals('Flate-Decode3'))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementString('CopyRight');
        $this->assert->boolean($element->contains('CopyRight'))->isEqualTo(true);
        $this->assert->boolean($element->contains('Copyright'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementString('CopyRight2');
        $this->assert->boolean($element->contains('CopyRight2'))->isEqualTo(true);
        $this->assert->boolean($element->contains('CopyRight3'))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementString('CopyRight');
        $this->assert->castToString($element)->isEqualTo('CopyRight');
    }
}

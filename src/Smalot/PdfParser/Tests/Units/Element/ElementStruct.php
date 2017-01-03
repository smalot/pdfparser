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
 * Class ElementStruct
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementStruct extends atoum\test
{
    public function testParse()
    {
        $document = new \Smalot\PdfParser\Document(array());

        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse('ABC', $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(
            ' [ << /Filter /FlateDecode >> ]',
            $document,
            $offset
        );
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(' / << /Filter /FlateDecode >> ', $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(' 0 << /Filter /FlateDecode >> ', $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(
            " 0 \n << /Filter /FlateDecode >> ",
            $document,
            $offset
        );
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(' << /Filter /FlateDecode >> ', $document, $offset);
        $this->assert->object($element)->isInstanceOf('\Smalot\PdfParser\Header');
        $this->assert->integer($offset)->isEqualTo(27);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(' << /Filter /FlateDecode >>', $document, $offset);
        $this->assert->object($element)->isInstanceOf('\Smalot\PdfParser\Header');
        $this->assert->integer($offset)->isEqualTo(27);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse('<< /Filter /FlateDecode >>', $document, $offset);
        $this->assert->object($element)->isInstanceOf('\Smalot\PdfParser\Header');
        $this->assert->integer($offset)->isEqualTo(26);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(
            " \n << /Filter /FlateDecode >> ",
            $document,
            $offset
        );
        $this->assert->object($element)->isInstanceOf('\Smalot\PdfParser\Header');
        $this->assert->integer($offset)->isEqualTo(29);
    }
}

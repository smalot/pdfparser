<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2020-06-02
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 *
 * @date    2017-01-03
 *
 * @license LGPLv3
 *
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

namespace PHPUnitTests\Integration\Element;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Element\ElementStruct;
use Smalot\PdfParser\Header;

class ElementStructTest extends TestCase
{
    public function testParse(): void
    {
        $document = $this->getDocumentInstance();

        // Skipped.
        $offset = 0;
        $element = ElementStruct::parse('ABC', $document, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementStruct::parse(' [ << /Filter /FlateDecode >> ]', $document, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementStruct::parse(' / << /Filter /FlateDecode >> ', $document, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementStruct::parse(' 0 << /Filter /FlateDecode >> ', $document, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementStruct::parse(" 0 \n << /Filter /FlateDecode >> ", $document, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        // Valid.
        $offset = 0;
        $element = ElementStruct::parse(' << /Filter /FlateDecode >> ', $document, $offset);
        $this->assertTrue($element instanceof Header);
        $this->assertEquals(27, $offset);

        $offset = 0;
        $element = ElementStruct::parse(' << /Filter /FlateDecode >>', $document, $offset);
        $this->assertTrue($element instanceof Header);
        $this->assertEquals(27, $offset);

        $offset = 0;
        $element = ElementStruct::parse('<< /Filter /FlateDecode >>', $document, $offset);
        $this->assertTrue($element instanceof Header);
        $this->assertEquals(26, $offset);

        $offset = 0;
        $element = ElementStruct::parse(" \n << /Filter /FlateDecode >> ", $document, $offset);
        $this->assertTrue($element instanceof Header);
        $this->assertEquals(29, $offset);
    }
}

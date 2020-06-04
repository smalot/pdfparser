<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2020-06-02
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

namespace Tests\Smalot\PdfParser\Integration\Element;

use Smalot\PdfParser\Element\ElementMissing;
use Test\Smalot\PdfParser\TestCase;

class ElementMissingTest extends TestCase
{
    public function testEquals()
    {
        $element = new ElementMissing();
        $this->assertFalse($element->equals(null));
        $this->assertFalse($element->equals(true));
        $this->assertFalse($element->equals('A'));
        $this->assertFalse($element->equals(false));
    }

    public function testGetContent()
    {
        $element = new ElementMissing();
        $this->assertFalse($element->getContent());
    }

    public function testContains()
    {
        $element = new ElementMissing();
        $this->assertFalse($element->contains(null));
        $this->assertFalse($element->contains(true));
        $this->assertFalse($element->contains('A'));
        $this->assertFalse($element->contains(false));
    }

    public function test__toString()
    {
        $element = new ElementMissing();
        $this->assertEquals('', (string) $element);
    }
}

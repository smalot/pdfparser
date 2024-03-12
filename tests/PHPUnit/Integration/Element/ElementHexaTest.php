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
use Smalot\PdfParser\Element\ElementDate;
use Smalot\PdfParser\Element\ElementHexa;
use Smalot\PdfParser\Element\ElementString;

class ElementHexaTest extends TestCase
{
    public function testParse(): void
    {
        // Skipped.
        $offset = 0;
        $element = ElementHexa::parse('ABC', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementHexa::parse(' [ <0020> 5 6 ]', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementHexa::parse(' << <0020> >>', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementHexa::parse(' / <0020> ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementHexa::parse(' 0 <0020> ', null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        $offset = 0;
        $element = ElementHexa::parse(" 0 \n <0020> ", null, $offset);
        $this->assertFalse($element);
        $this->assertEquals(0, $offset);

        // Valid.
        $offset = 0;
        $element = ElementHexa::parse(' <0020> ', null, $offset);
        $this->assertEquals(' ', $element->getContent());
        $this->assertEquals(7, $offset);

        $offset = 0;
        $element = ElementHexa::parse(' <0020> ', null, $offset);
        $this->assertEquals(' ', $element->getContent());
        $this->assertEquals(7, $offset);

        $offset = 0;
        $element = ElementHexa::parse(' <0020>', null, $offset);
        $this->assertEquals(' ', $element->getContent());
        $this->assertEquals(7, $offset);

        $offset = 0;
        $element = ElementHexa::parse('<0020>', null, $offset);
        $this->assertEquals(' ', $element->getContent());
        $this->assertEquals(6, $offset);

        $offset = 0;
        $element = ElementHexa::parse(" \n <0020> ", null, $offset);
        $this->assertEquals(' ', $element->getContent());
        $this->assertEquals(9, $offset);

        $offset = 0;
        $element = ElementHexa::parse(" \n <5465616d204d616e6167656d656e742053797374656d73> ", null, $offset);
        $this->assertEquals('Team Management Systems', $element->getContent());
        $this->assertEquals(51, $offset);

        $offset = 0;
        $element = ElementHexa::parse(" \n <5265706f72744275696c646572> ", null, $offset);
        $this->assertTrue($element instanceof ElementString);
        $this->assertEquals('ReportBuilder', $element->getContent());
        $this->assertEquals(31, $offset);

        $offset = 0;
        $element = ElementHexa::parse(" \n <443a3230313331323137313334303435303027303027> ", null, $offset);
        $this->assertTrue($element instanceof ElementDate);
        $this->assertEquals('2013-12-17T13:40:45+00:00', (string) $element);
        $this->assertEquals(49, $offset);

        // Test that a hexadecimal string 'dirty' with extra characters
        // such as newlines or spaces is properly decoded
        $element = ElementHexa::decode(' <feff007000610073007100750061002c0020007000720069006d00610076006500720061002c0020
00720065007  30075007200720065007a0069006f006e0065002c0020006600650073007400610020
0063007200690073007400690061006e0061002c002000670065007300f9002c00200075006f0076
0061002000640069 & 002000630069006f00630063006f006c00610074006100 Y 2c00200063006f006e
00690067006c00690065007400740069002c0020007000750 / 06c00630069006e0069002c00200070
00610073007100750061006c0065  002c002000630061006d00700061006e0065002c002000640069
006e006100200072006500620075006300630069002c00200075006f007600610020006400690020
007000610  073007100750061002c0020> ');

        $this->assertEquals('pasqua, primavera, resurrezione, festa cristiana, gesù, uova di cioccolata, coniglietti, pulcini, pasquale, campane, dina rebucci, uova di pasqua, ', $element);
    }
}

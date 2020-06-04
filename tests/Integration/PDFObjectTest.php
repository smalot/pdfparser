<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2020-06-01
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

namespace Tests\Smalot\PdfParser\Integration;

use Smalot\PdfParser\Document;
use Smalot\PdfParser\PDFObject;
use Test\Smalot\PdfParser\TestCase;

class PDFObjectTest extends TestCase
{
    const TYPE = 't';

    const OPERATOR = 'o';

    const COMMAND = 'c';

    protected function getPdfObjectInstance($document)
    {
        return new PDFObject($document);
    }

    public function testGetCommandsText()
    {
        $content = "/R14 30 Tf 0.999016 0 0 1 137.4
342.561 Tm
[(A)-168.854( BC D)-220.905(\\(E\\))20.905<20>]
TJ /R14 17.16 Tf <20> Tj
0.999014 0 0 1 336.84 319.161 Tm T* ( \x00m)Tj
/R14 20.04 Tf
ET Q
q -124.774 124.127 5.64213 5.67154 930.307 4436.95 cm
BI";

        $offset = 0;
        $parts = $this->getPdfObjectInstance(new Document())->getCommandsText($content, $offset);
        $reference = [
            [
                self::TYPE => '/',
                self::OPERATOR => 'Tf',
                self::COMMAND => 'R14 30',
            ],
            [
                self::TYPE => '',
                self::OPERATOR => 'Tm',
                self::COMMAND => "0.999016 0 0 1 137.4\n342.561",
            ],
            [
                self::TYPE => '[',
                self::OPERATOR => 'TJ',
                self::COMMAND => [
                    [
                        self::TYPE => '(',
                        self::OPERATOR => '',
                        self::COMMAND => 'A',
                    ],
                    [
                        self::TYPE => 'n',
                        self::OPERATOR => '',
                        self::COMMAND => '-168.854',
                    ],
                    [
                        self::TYPE => '(',
                        self::OPERATOR => '',
                        self::COMMAND => ' BC D',
                    ],
                    [
                        self::TYPE => 'n',
                        self::OPERATOR => '',
                        self::COMMAND => '-220.905',
                    ],
                    [
                        self::TYPE => '(',
                        self::OPERATOR => '',
                        self::COMMAND => '\\(E\\)',
                    ],
                    [
                        self::TYPE => 'n',
                        self::OPERATOR => '',
                        self::COMMAND => '20.905',
                    ],
                    [
                        self::TYPE => '<',
                        self::OPERATOR => '',
                        self::COMMAND => '20',
                    ],
                ],
            ],
            [
                self::TYPE => '/',
                self::OPERATOR => 'Tf',
                self::COMMAND => 'R14 17.16',
            ],
            [
                self::TYPE => '<',
                self::OPERATOR => 'Tj',
                self::COMMAND => '20',
            ],
            [
                self::TYPE => '',
                self::OPERATOR => 'Tm',
                self::COMMAND => '0.999014 0 0 1 336.84 319.161',
            ],
            [
                self::TYPE => '',
                self::OPERATOR => 'T*',
                self::COMMAND => '',
            ],
            [
                self::TYPE => '(',
                self::OPERATOR => 'Tj',
                self::COMMAND => " \x00m",
            ],
            [
                self::TYPE => '/',
                self::OPERATOR => 'Tf',
                self::COMMAND => 'R14 20.04',
            ],
        ];

        $this->assertEquals($parts, $reference);
        $this->assertEquals(172, $offset);
    }

    public function testCleanContent()
    {
        $content = '/Shape <</MCID << /Font<8>>> BT >>BDC
Q
/CS0 cs 1 1 0  scn
1 i
/GS0 gs
BT
/TT0 1 Tf
0.0007 Tc 0.0018 Tw 0  Ts 100  Tz 0 Tr 24 0 0 24 51.3 639.26025 Tm
(Modificatio[ns] au \\(14\\) septembre 2009 ET 2010)Tj
EMC
(ABC) Tj

[ (a)-4.5(b)6(c)8.8 ( fsdfsdfsdf[]sd) ] TD

ET
/Shape <</MCID 2 >>BDC
q
0.03 841';

        $expected = '_____________________________________
Q
/CS0 cs 1 1 0  scn
1 i
/GS0 gs
BT
/TT0 1 Tf
0.0007 Tc 0.0018 Tw 0  Ts 100  Tz 0 Tr 24 0 0 24 51.3 639.26025 Tm
(________________________________________________)Tj
___
(___) Tj

[_____________________________________] TD

ET
______________________
q
0.03 841';

        $cleaned = $this->getPdfObjectInstance(new Document())->cleanContent($content, '_');

        $this->assertEquals($cleaned, $expected);
    }

    public function testGetSectionText()
    {
        $content = '/Shape <</MCID 1 >>BDC
Q
/CS0 cs 1 1 0  scn
1 i
/GS0 gs
BT
/TT0 1 Tf
0.0007 Tc 0.0018 Tw 0  Ts 100  Tz 0 Tr 24 0 0 24 51.3 639.26025 Tm
(Mod BT atio[ns] au \\(14\\) septembre 2009 ET 2010)Tj
EMC
(ABC) Tj

[ (a)-4.5(b) 6(c)8.8 ( fsdfsdfsdf[ sd) ] TD

ET
/Shape <</MCID [BT] >>BDC BT /TT1 1.5 Tf (BT )Tj ET
q
0.03 841';

        $sections = $this->getPdfObjectInstance(new Document())->getSectionsText($content);

        $this->assertEquals(
            ['/TT0 1 Tf
0.0007 Tc 0.0018 Tw 0  Ts 100  Tz 0 Tr 24 0 0 24 51.3 639.26025 Tm
(Mod BT atio[ns] au \(14\) septembre 2009 ET 2010)Tj
EMC
(ABC) Tj

[ (a)-4.5(b) 6(c)8.8 ( fsdfsdfsdf[ sd) ] TD ', '/TT1 1.5 Tf (BT )Tj '],
            $sections
        );
    }
}

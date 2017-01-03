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

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Object
 *
 * @package Smalot\PdfParser\Tests\Units
 */
class Object extends atoum\test
{
    const TYPE = 't';

    const OPERATOR = 'o';

    const COMMAND = 'c';

    public function testGetTextParts()
    {
    }

//    public function testGetCommandsImage()
//    {
//        $content = "/CS/RGB
///W 22
///H 1
///BPC 8
///F/Fl
///DP<</Predictor 15
///Columns 22
///Colors 3>>
//ID \x00\x50c\x63
//EI Q
//q -124.774 124.127 5.64213 5.67154 930.307 4436.95 cm
//BI
//";
//
//        $document  = new \Smalot\PdfParser\Document();
//        $object    = new \Smalot\PdfParser\Object($document);
//        $offset    = 0;
//        $parts     = $object->getCommandsImage($content, $offset);
//        $reference = array(
//            array(
//                self::TYPE => '/',
//                self::OPERATOR => 'CS',
//                self::COMMAND => 'RGB',
//            ),
//            array(
//                self::TYPE => '/',
//                self::OPERATOR => 'W',
//                self::COMMAND => '22',
//            ),
//            array(
//                self::TYPE => '/',
//                self::OPERATOR => 'H',
//                self::COMMAND => '1',
//            ),
//            array(
//                self::TYPE => '/',
//                self::OPERATOR => 'BPC',
//                self::COMMAND => '8',
//            ),
//            array(
//                self::TYPE => '/',
//                self::OPERATOR => 'F',
//                self::COMMAND => 'Fl',
//            ),
//            array(
//                self::TYPE => 'struct',
//                self::OPERATOR => 'DP',
//                self::COMMAND => array(
//                    array(
//                        self::TYPE => '/',
//                        self::OPERATOR => 'Predictor',
//                        self::COMMAND => '15',
//                    ),
//                    array(
//                        self::TYPE => '/',
//                        self::OPERATOR => 'Columns',
//                        self::COMMAND => '22',
//                    ),
//                    array(
//                        self::TYPE => '/',
//                        self::OPERATOR => 'Colors',
//                        self::COMMAND => '3',
//                    ),
//                ),
//            ),
//            array(
//                self::TYPE => '',
//                self::OPERATOR => 'ID',
//                self::COMMAND => "\x00\x50c\x63",
//            ),
//        );
//
//        $this->assert->array($parts)->isEqualTo($reference);
//        $this->assert->integer($offset)->isEqualTo(83);
//    }

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

        $document  = new \Smalot\PdfParser\Document();
        $object    = new \Smalot\PdfParser\Object($document);
        $offset    = 0;
        $parts     = $object->getCommandsText($content, $offset);
        $reference = array(
            array(
                self::TYPE     => '/',
                self::OPERATOR => 'Tf',
                self::COMMAND  => 'R14 30',
            ),
            array(
                self::TYPE     => '',
                self::OPERATOR => 'Tm',
                self::COMMAND  => "0.999016 0 0 1 137.4\n342.561",
            ),
            array(
                self::TYPE     => '[',
                self::OPERATOR => 'TJ',
                self::COMMAND  => array(
                    array(
                        self::TYPE     => '(',
                        self::OPERATOR => '',
                        self::COMMAND  => 'A',
                    ),
                    array(
                        self::TYPE     => 'n',
                        self::OPERATOR => '',
                        self::COMMAND  => '-168.854',
                    ),
                    array(
                        self::TYPE     => '(',
                        self::OPERATOR => '',
                        self::COMMAND  => ' BC D',
                    ),
                    array(
                        self::TYPE     => 'n',
                        self::OPERATOR => '',
                        self::COMMAND  => '-220.905',
                    ),
                    array(
                        self::TYPE     => '(',
                        self::OPERATOR => '',
                        self::COMMAND  => '\\(E\\)',
                    ),
                    array(
                        self::TYPE     => 'n',
                        self::OPERATOR => '',
                        self::COMMAND  => '20.905',
                    ),
                    array(
                        self::TYPE     => '<',
                        self::OPERATOR => '',
                        self::COMMAND  => '20',
                    ),
                ),
            ),
            array(
                self::TYPE     => '/',
                self::OPERATOR => 'Tf',
                self::COMMAND  => 'R14 17.16',
            ),
            array(
                self::TYPE     => '<',
                self::OPERATOR => 'Tj',
                self::COMMAND  => '20',
            ),
            array(
                self::TYPE     => '',
                self::OPERATOR => 'Tm',
                self::COMMAND  => '0.999014 0 0 1 336.84 319.161',
            ),
            array(
                self::TYPE     => '',
                self::OPERATOR => 'T*',
                self::COMMAND  => '',
            ),
            array(
                self::TYPE     => '(',
                self::OPERATOR => 'Tj',
                self::COMMAND  => " \x00m",
            ),
            array(
                self::TYPE     => '/',
                self::OPERATOR => 'Tf',
                self::COMMAND  => 'R14 20.04',
            ),
        );

        $this->assert->array($parts)->isEqualTo($reference);
        $this->assert->integer($offset)->isEqualTo(172);
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

        $document = new \Smalot\PdfParser\Document();
        $object   = new \Smalot\PdfParser\Object($document);
        $cleaned  = $object->cleanContent($content, '_');

        $this->assert->string($cleaned)->length->isEqualTo(strlen($content));
        $this->assert->string($cleaned)->isEqualTo($expected);
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

        $document = new \Smalot\PdfParser\Document();
        $object   = new \Smalot\PdfParser\Object($document);
        $sections = $object->getSectionsText($content);

//        $this->assert->string($cleaned)->length->isEqualTo(strlen($content));
//        $this->assert->string($cleaned)->isEqualTo($expected);
    }
}

<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-2.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $this->assert->castToString($element)->isEqualTo('ReportBuilder');
        $this->assert->object($element)->isInstanceOf('\Smalot\PdfParser\Element\ElementDate');
        $this->assert->castToString($element)->isEqualTo('2013-12-17T13:40:45+00:00');
        $this->assert->integer($offset)->isEqualTo(49);
    }
}

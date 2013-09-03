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
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementHexa('0020');
        $this->assert->string($element->getContent())->isEqualTo(' ');
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementHexa('0020');
        $this->assert->boolean($element->equals(' '))->isEqualTo(true);
        $this->assert->boolean($element->equals('A'))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementHexa('0020');
        $this->assert->boolean($element->contains(' '))->isEqualTo(true);
        $this->assert->boolean($element->contains('A'))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementHexa('0020');
        $this->assert->castToString($element)->isEqualTo(' ');
    }
}

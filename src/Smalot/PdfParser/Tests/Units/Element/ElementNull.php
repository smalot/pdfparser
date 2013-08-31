<?php

/**
 * @file
 * This file is part of the PdfParser library.
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
 * Class ElementNull
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementNull extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse('ABC', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse(' [ null ]', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse(' << null >>', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse(' / null ', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse(' 0 null ', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse(" 0 \n null ", null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse(' null ', null, $offset);
        $this->boolean(is_null($element->getContent()))->isEqualTo(true);
        $this->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse(' null ', null, $offset);
        $this->boolean(is_null($element->getContent()))->isEqualTo(true);
        $this->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse(' null', null, $offset);
        $this->boolean(is_null($element->getContent()))->isEqualTo(true);
        $this->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse('null', null, $offset);
        $this->boolean(is_null($element->getContent()))->isEqualTo(true);
        $this->integer($offset)->isEqualTo(4);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNull::parse(" \n null ", null, $offset);
        $this->boolean(is_null($element->getContent()))->isEqualTo(true);
        $this->integer($offset)->isEqualTo(7);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementNull('null');
        $this->boolean(is_null($element->getContent()))->isEqualTo(true);
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementNull('null');
        $this->boolean($element->equals(null))->isEqualTo(true);
        $this->boolean($element->equals(false))->isEqualTo(false);
        $this->boolean($element->equals(0))->isEqualTo(false);
        $this->boolean($element->equals(1))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementNull('null');
        $this->boolean($element->contains(null))->isEqualTo(true);
        $this->boolean($element->contains(false))->isEqualTo(false);
        $this->boolean($element->contains(0))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementNull('null');
        $this->string($element->__toString())->isEqualTo('null');
    }
}

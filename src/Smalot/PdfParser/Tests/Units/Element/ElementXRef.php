<?php

namespace Smalot\PdfParser\Tests\Units\Element;

use mageekguy\atoum;

class ElementXRef extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse('ABC', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' [ 5 0 R ]', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' << 5 0 R >>', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' / 5 0 R ', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' 0 5 0 R ', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(" 0 \n 5 0 R ", null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' 5 0 R ', null, $offset);
        $this->string($element->getContent())->isEqualTo('5 0 R');
        $this->integer($offset)->isEqualTo(6);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' 5 0 R ', null, $offset);
        $this->string($element->getContent())->isEqualTo('5 0 R');
        $this->integer($offset)->isEqualTo(6);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(' 5 0 R', null, $offset);
        $this->string($element->getContent())->isEqualTo('5 0 R');
        $this->integer($offset)->isEqualTo(6);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse('5 0 R', null, $offset);
        $this->string($element->getContent())->isEqualTo('5 0 R');
        $this->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementXRef::parse(" \n 5 0 R ", null, $offset);
        $this->string($element->getContent())->isEqualTo('5 0 R');
        $this->integer($offset)->isEqualTo(8);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementXRef('5 0 R');
        $this->string($element->getContent())->isEqualTo('5 0 R');
    }

    public function testGetId()
    {
        $element = new \Smalot\PdfParser\Element\ElementXRef('5 0 R');
        $this->integer($element->getId())->isEqualTo(5);
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementXRef('5 0 R');
        $this->boolean($element->equals(5))->isEqualTo(true);
        $this->boolean($element->equals(8))->isEqualTo(false);
        $this->boolean($element->equals($element))->isEqualTo(true);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementXRef('5 0 R');
        $this->boolean($element->contains(5))->isEqualTo(true);
        $this->boolean($element->contains(8))->isEqualTo(false);
        $this->boolean($element->contains($element))->isEqualTo(true);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementXRef('5 0 R');
        $this->string($element->__toString())->isEqualTo('#Obj#5');
    }
}

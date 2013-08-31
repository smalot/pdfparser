<?php

namespace Smalot\PdfParser\Tests\Units\Element;

use mageekguy\atoum;

class ElementMissing extends atoum\test
{
    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementMissing(null);
        $this->boolean($element->equals(null))->isEqualTo(false);
        $this->boolean($element->equals(true))->isEqualTo(false);
        $this->boolean($element->equals('A'))->isEqualTo(false);
        $this->boolean($element->equals(false))->isEqualTo(false);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementMissing(null);
        $this->boolean($element->getContent())->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementMissing(null);
        $this->boolean($element->contains(null))->isEqualTo(false);
        $this->boolean($element->contains(true))->isEqualTo(false);
        $this->boolean($element->contains('A'))->isEqualTo(false);
        $this->boolean($element->contains(false))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementMissing(null);
        $this->string($element->__toString())->isEqualTo('');
    }
}

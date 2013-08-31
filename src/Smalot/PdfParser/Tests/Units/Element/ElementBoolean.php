<?php

namespace Smalot\PdfParser\Tests\Units\Element;

use mageekguy\atoum;

class ElementBoolean extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse('ABC', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' [ false ]', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' << true >>', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' / false ', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' 0 true ', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(" 0 \n true ", null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' true ', null, $offset);
        $this->boolean($element->getContent())->isEqualTo(true);
        $this->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' TRUE ', null, $offset);
        $this->boolean($element->getContent())->isEqualTo(true);
        $this->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' True', null, $offset);
        $this->boolean($element->getContent())->isEqualTo(true);
        $this->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse('true', null, $offset);
        $this->boolean($element->getContent())->isEqualTo(true);
        $this->integer($offset)->isEqualTo(4);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse('False', null, $offset);
        $this->boolean($element->getContent())->isEqualTo(false);
        $this->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(" \n true ", null, $offset);
        $this->boolean($element->getContent())->isEqualTo(true);
        $this->integer($offset)->isEqualTo(7);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementBoolean('true');
        $this->boolean($element->getContent())->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementBoolean('false');
        $this->boolean($element->getContent())->isEqualTo(false);
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementBoolean('true');
        $this->boolean($element->equals(true))->isEqualTo(true);
        $this->boolean($element->equals(1))->isEqualTo(false);
        $this->boolean($element->equals(false))->isEqualTo(false);
        $this->boolean($element->equals(null))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementBoolean('false');
        $this->boolean($element->equals(false))->isEqualTo(true);
        $this->boolean($element->equals(0))->isEqualTo(false);
        $this->boolean($element->equals(true))->isEqualTo(false);
        $this->boolean($element->equals(null))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementBoolean('true');
        $this->boolean($element->contains(true))->isEqualTo(true);
        $this->boolean($element->contains(false))->isEqualTo(false);
        $this->boolean($element->contains(1))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementBoolean('true');
        $this->string($element->__toString())->isEqualTo('true');
        $element = new \Smalot\PdfParser\Element\ElementBoolean('false');
        $this->string($element->__toString())->isEqualTo('false');
    }
}

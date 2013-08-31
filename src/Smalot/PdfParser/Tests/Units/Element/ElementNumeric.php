<?php

namespace Smalot\PdfParser\Tests\Units\Element;

use mageekguy\atoum;

class ElementNumeric extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse('ABC', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' [ 2 ]', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' /2', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(" /2 \n 2", null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' -2', null, $offset);
        $this->float($element->getContent())->isEqualTo(-2.0);
        $this->integer($offset)->isEqualTo(3);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse('2BC', null, $offset);
        $this->float($element->getContent())->isEqualTo(2.0);
        $this->integer($offset)->isEqualTo(1);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' 2BC', null, $offset);
        $this->float($element->getContent())->isEqualTo(2.0);
        $this->integer($offset)->isEqualTo(2);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' -2BC', null, $offset);
        $this->float($element->getContent())->isEqualTo(-2.0);
        $this->integer($offset)->isEqualTo(3);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' -2', null, $offset);
        $this->float($element->getContent())->isEqualTo(-2.0);
        $this->integer($offset)->isEqualTo(3);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(' 2 0 obj', null, $offset);
        $this->float($element->getContent())->isEqualTo(2.0);
        $this->integer($offset)->isEqualTo(2);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementNumeric::parse(" \n -2 ", null, $offset);
        $this->float($element->getContent())->isEqualTo(-2.0);
        $this->integer($offset)->isEqualTo(5);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementNumeric('B');
        $this->float($element->getContent())->isEqualTo(0.0);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->float($element->getContent())->isEqualTo(-2.5);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->float($element->getContent())->isEqualTo(-2.0);
        $element = new \Smalot\PdfParser\Element\ElementNumeric(' -2');
        $this->float($element->getContent())->isEqualTo(-2.0);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->float($element->getContent())->isEqualTo(2.5);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->float($element->getContent())->isEqualTo(2.0);
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementNumeric('1');
        $this->boolean($element->equals('B'))->isEqualTo(false);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('1.5');
        $this->boolean($element->equals('B'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->boolean($element->equals('2'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->boolean($element->equals('3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->boolean($element->equals('-2'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->boolean($element->equals('-3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->boolean($element->equals('2.5'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->boolean($element->equals('3.5'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->boolean($element->equals('-2.5'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->boolean($element->equals('-3.5'))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementNumeric('1');
        $this->boolean($element->contains('B'))->isEqualTo(false);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('1.5');
        $this->boolean($element->contains('B'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->boolean($element->contains('2'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->boolean($element->contains('3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->boolean($element->contains('-2'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->boolean($element->contains('-3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->boolean($element->contains('2.5'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->boolean($element->contains('3.5'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->boolean($element->contains('-2.5'))->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->boolean($element->contains('-3.5'))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementNumeric('B');
        $this->string($element->__toString())->isEqualTo('0');
        $element = new \Smalot\PdfParser\Element\ElementNumeric('1B');
        $this->string($element->__toString())->isEqualTo('1');

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $this->string($element->__toString())->isEqualTo('2');

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2');
        $this->string($element->__toString())->isEqualTo('-2');

        $element = new \Smalot\PdfParser\Element\ElementNumeric('2.5');
        $this->string($element->__toString())->isEqualTo('2.5');

        $element = new \Smalot\PdfParser\Element\ElementNumeric('-2.5');
        $this->string($element->__toString())->isEqualTo('-2.5');
    }
}

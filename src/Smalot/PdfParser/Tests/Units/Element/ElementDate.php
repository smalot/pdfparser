<?php

namespace Smalot\PdfParser\Tests\Units\Element;

use mageekguy\atoum;

class ElementDate extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse('ABC', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' [ (D:ABC) 5 6 ]', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' << (D:invalid) >>', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' / (D:FlateDecode) ', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' 0 (D:FlateDecode) ', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(" 0 \n (D:FlateDecode) ", null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' (D:2013-05-15T15:35:02) ', null, $offset);
        $this->string($element->getContent())->isEqualTo('D:2013-05-15T15:35:02');
        $this->integer($offset)->isEqualTo(24);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' (D:2013-05-15T15:35:02) ', null, $offset);
        $this->string($element->getContent())->isEqualTo('D:2013-05-15T15:35:02');
        $this->integer($offset)->isEqualTo(24);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(' (D:2013-05-15T15:35:02)', null, $offset);
        $this->string($element->getContent())->isEqualTo('D:2013-05-15T15:35:02');
        $this->integer($offset)->isEqualTo(24);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse('(D:2013-05-15T15:35:02)', null, $offset);
        $this->string($element->getContent())->isEqualTo('D:2013-05-15T15:35:02');
        $this->integer($offset)->isEqualTo(23);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementDate::parse(" \n (D:2013-05-15T15:35:02) ", null, $offset);
        $this->string($element->getContent())->isEqualTo('D:2013-05-15T15:35:02');
        $this->integer($offset)->isEqualTo(26);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementDate('D:2013-05-15T15:35:02');
        $this->string($element->getContent())->isEqualTo('D:2013-05-15T15:35:02');
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementDate('D:2013-05-15T15:35:02');
        $this->boolean($element->equals('D:2013-05-15T15:35:02'))->isEqualTo(true);
        $this->boolean($element->equals('D:ABC'))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementDate('D:2013-05-15T15:35:02');
        $this->boolean($element->contains('D:2013-05-15T15:35:02'))->isEqualTo(true);
        $this->boolean($element->contains('D:2013-06-66'))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementDate('D:2013-05-15T15:35:02');
        $this->string($element->__toString())->isEqualTo('D:2013-05-15T15:35:02');
    }
}

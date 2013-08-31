<?php

namespace Smalot\PdfParser\Tests\Units\Element;

use mageekguy\atoum;

//require_once __DIR__ . '/../../../../../vendor/autoload.php';

class ElementArray extends atoum\test
{
    public function testParse()
    {
        $document = new \Smalot\PdfParser\Document(array());

        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse('ABC', $document, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' / [ 4 2 ] ', $document, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' 0 [ 4 2 ] ', $document, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(" 0 \n [ 4 2 ] ", $document, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' [ 4 2 ] ', $document, $offset);
        $this->boolean($element->contains(4))->isEqualTo(true);
        $this->boolean($element->contains(2))->isEqualTo(true);
        $this->boolean($element->contains(8))->isEqualTo(false);
        $this->integer($offset)->isEqualTo(8);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' [ 4 2 ]', $document, $offset);
        $this->boolean($element->contains(4))->isEqualTo(true);
        $this->boolean($element->contains(2))->isEqualTo(true);
        $this->boolean($element->contains(8))->isEqualTo(false);
        $this->integer($offset)->isEqualTo(8);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse('[ 4 2 ]', $document, $offset);
        $this->boolean($element->contains(4))->isEqualTo(true);
        $this->boolean($element->contains(2))->isEqualTo(true);
        $this->boolean($element->contains(8))->isEqualTo(false);
        $this->integer($offset)->isEqualTo(7);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(" \n [ 4 2 ] ", $document, $offset);
        $this->boolean($element->contains(4))->isEqualTo(true);
        $this->boolean($element->contains(2))->isEqualTo(true);
        $this->boolean($element->contains(8))->isEqualTo(false);
        $this->integer($offset)->isEqualTo(10);
    }

    public function testGetContent()
    {
        $val_4   = new \Smalot\PdfParser\Element\ElementNumeric('4');
        $val_2   = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $element = new \Smalot\PdfParser\Element\ElementArray(array($val_4, $val_2));

        $content = $element->getContent();
        $this->boolean(is_array($content))->isEqualTo(true);
    }

    public function testContains()
    {
        $val_4   = new \Smalot\PdfParser\Element\ElementNumeric('4');
        $val_2   = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $element = new \Smalot\PdfParser\Element\ElementArray(array($val_4, $val_2));

        $this->boolean($element->contains(2))->isEqualTo(true);
        $this->boolean($element->contains(8))->isEqualTo(false);
    }

    public function testResolveXRef()
    {
        $document = new \Smalot\PdfParser\Document();
        $ref      = \Smalot\PdfParser\Object::parse($document, 'hello');
        $document->setObjects(array(1 => $ref));

        $element = \Smalot\PdfParser\Element\ElementArray::parse(' [ 1 0 R ]', $document);
        /** @var \Smalot\PdfParser\Object $content */
        $content = current($element->getContent());
        $this->boolean($content instanceof \Smalot\PdfParser\Object)->isEqualTo(true);
        $this->string($content->getContent())->isEqualTo('hello');
    }

    public function test__toString()
    {
        $val_4   = new \Smalot\PdfParser\Element\ElementNumeric('4');
        $val_2   = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $element = new \Smalot\PdfParser\Element\ElementArray(array($val_4, $val_2));
        $this->string($element->__toString())->isEqualTo('4,2');

        $document = new \Smalot\PdfParser\Document(array());
        $element  = \Smalot\PdfParser\Element\ElementArray::parse(' [ 4 2 ]', $document);
        $this->string($element->__toString())->isEqualTo('4,2');
    }
}

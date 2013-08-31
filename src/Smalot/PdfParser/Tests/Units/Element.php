<?php

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

class Element extends atoum\test
{
    public function testParse()
    {
        $document = new \Smalot\PdfParser\Document(array());

        // only_values = false
        $content  = '/NameType /FlateDecode
        /Contents[4 0 R 42]/Fonts<</F1 41/F2 43>>/NullType
        null/StringType(hello)/DateType(D:2013)/XRefType 2 0 R
        /NumericType 8/HexaType<0020>/BooleanType false';
        $offset   = 0;
        $elements = \Smalot\PdfParser\Element::parse($content, $document, $offset, false);

        $this->boolean(array_key_exists('NameType', $elements))->isEqualTo(true);
        $this->boolean($elements['NameType'] instanceof \Smalot\PdfParser\Element\ElementName)->isEqualTo(true);
        $this->string($elements['NameType']->getContent())->isEqualTo('FlateDecode');

        $this->boolean(array_key_exists('Contents', $elements))->isEqualTo(true);
        $this->boolean($elements['Contents'] instanceof \Smalot\PdfParser\Element\ElementArray)->isEqualTo(true);
        $this->boolean($elements['Contents']->contains(42))->isEqualTo(true);

        $this->boolean(array_key_exists('Fonts', $elements))->isEqualTo(true);
        $this->boolean($elements['Fonts'] instanceof \Smalot\PdfParser\Object)->isEqualTo(true);

        $this->boolean(array_key_exists('NullType', $elements))->isEqualTo(true);
        $this->boolean($elements['NullType'] instanceof \Smalot\PdfParser\Element\ElementNull)->isEqualTo(true);
        $this->string($elements['NullType']->__toString())->isEqualTo('null');

        $this->boolean(array_key_exists('StringType', $elements))->isEqualTo(true);
        $this->boolean($elements['StringType'] instanceof \Smalot\PdfParser\Element\ElementString)->isEqualTo(true);
        $this->string($elements['StringType']->getContent())->isEqualTo('hello');

        $this->boolean(array_key_exists('DateType', $elements))->isEqualTo(true);
        $this->boolean($elements['DateType'] instanceof \Smalot\PdfParser\Element\ElementDate)->isEqualTo(true);
        $this->string($elements['DateType']->getContent())->isEqualTo('D:2013');

        $this->boolean(array_key_exists('XRefType', $elements))->isEqualTo(true);
        $this->boolean($elements['XRefType'] instanceof \Smalot\PdfParser\Element\ElementXRef)->isEqualTo(true);
        $this->integer($elements['XRefType']->getId())->isEqualTo(2);

        $this->boolean(array_key_exists('NumericType', $elements))->isEqualTo(true);
        $this->boolean($elements['NumericType'] instanceof \Smalot\PdfParser\Element\ElementNumeric)->isEqualTo(true);
        $this->string($elements['NumericType']->__toString())->isEqualTo('8');

        $this->boolean(array_key_exists('HexaType', $elements))->isEqualTo(true);
        $this->boolean($elements['HexaType'] instanceof \Smalot\PdfParser\Element\ElementHexa)->isEqualTo(true);
        $this->string($elements['HexaType']->getContent())->isEqualTo(' ');

        $this->boolean(array_key_exists('BooleanType', $elements))->isEqualTo(true);
        $this->boolean($elements['BooleanType'] instanceof \Smalot\PdfParser\Element\ElementBoolean)->isEqualTo(true);
        $this->boolean($elements['BooleanType']->getContent())->isEqualTo(false);

        // only_values = true
        $content  = '/NameType /FlateDecode';
        $offset   = 0;
        $elements = \Smalot\PdfParser\Element::parse($content, $document, $offset, true);

        // test error
        $content  = '/NameType /FlateDecode $$$';
        $offset   = 0;
        $elements = \Smalot\PdfParser\Element::parse($content, $document, $offset, false);


        $content  = '/NameType $$$';
        $offset   = 0;
        try {
            $elements = \Smalot\PdfParser\Element::parse($content, $document, $offset, false);
            $this->exception(null);
        } catch(\Exception $e) {
            $this->exception($e);
        }
        //var_dump($elements);

        /*$this->boolean(array_key_exists('NameType', $elements))->isEqualTo(true);
        $this->boolean($elements['NameType'] instanceof \Smalot\PdfParser\Element\ElementName)->isEqualTo(true);
        $this->string($elements['NameType']->getContent())->isEqualTo('FlateDecode');*/
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element(42);
        $content = $element->getContent();
        $this->integer($content)->isEqualTo(42);

        $element = new \Smalot\PdfParser\Element(array(4, 2));
        $content = $element->getContent();
        $this->boolean(is_array($content))->isEqualTo(true);
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element(2);

        $this->boolean($element->equals(2))->isEqualTo(true);
        $this->boolean($element->equals(8))->isEqualTo(false);
    }

    public function testContains()
    {
        $val_4   = new \Smalot\PdfParser\Element(4);
        $val_2   = new \Smalot\PdfParser\Element(2);
        $element = new \Smalot\PdfParser\Element(array($val_4, $val_2));

        $this->boolean($element->contains(2))->isEqualTo(true);
        $this->boolean($element->contains(8))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element(2);
        $this->string($element->__toString())->isEqualTo('2');
    }
}

<?php

namespace Smalot\PdfParser\Tests\Units\Element;

use mageekguy\atoum;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Object;

class ElementStruct extends atoum\test
{
    public function testParse()
    {
        $document = new Document(array());

        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse('ABC', $document, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(' [ << /Filter /FlateDecode >> ]', $document, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(' / << /Filter /FlateDecode >> ', $document, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(' 0 << /Filter /FlateDecode >> ', $document, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(" 0 \n << /Filter /FlateDecode >> ", $document, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(' << /Filter /FlateDecode >> ', $document, $offset);
        $this->boolean($element instanceof Object)->isEqualTo(true);
        $this->integer($offset)->isEqualTo(27);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(' << /Filter /FlateDecode >>', $document, $offset);
        $this->boolean($element instanceof Object)->isEqualTo(true);
        $this->integer($offset)->isEqualTo(27);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse('<< /Filter /FlateDecode >>', $document, $offset);
        $this->boolean($element instanceof Object)->isEqualTo(true);
        $this->integer($offset)->isEqualTo(26);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementStruct::parse(" \n << /Filter /FlateDecode >> ", $document, $offset);
        $this->boolean($element instanceof Object)->isEqualTo(true);
        $this->integer($offset)->isEqualTo(29);
    }
}

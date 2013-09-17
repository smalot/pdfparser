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
 * Class ElementArray
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementArray extends atoum\test
{
    public function testParse()
    {
        $document = new \Smalot\PdfParser\Document(array());

        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse('ABC', $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' / [ 4 2 ] ', $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' 0 [ 4 2 ] ', $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(" 0 \n [ 4 2 ] ", $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' [ 4 2 ] ', $document, $offset);
        $this->assert->boolean($element->contains(4))->isEqualTo(true);
        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(8);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' [ 4 2 ]', $document, $offset);
        $this->assert->boolean($element->contains(4))->isEqualTo(true);
        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(8);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse('[ 4 2 ]', $document, $offset);
        $this->assert->boolean($element->contains(4))->isEqualTo(true);
        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(7);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(" \n [ 4 2 ] ", $document, $offset);
        $this->assert->boolean($element->contains(4))->isEqualTo(true);
        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(10);
    }

    public function testGetContent()
    {
        $val_4   = new \Smalot\PdfParser\Element\ElementNumeric('4');
        $val_2   = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $element = new \Smalot\PdfParser\Element\ElementArray(array($val_4, $val_2));

        $content = $element->getContent();
        $this->assert->array($content)->hasSize(2);
    }

    public function testContains()
    {
        $val_4   = new \Smalot\PdfParser\Element\ElementNumeric('4');
        $val_2   = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $element = new \Smalot\PdfParser\Element\ElementArray(array($val_4, $val_2));

        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
    }

    public function testResolveXRef()
    {
        $document = new \Smalot\PdfParser\Document();
        $ref      = \Smalot\PdfParser\Object::parse($document, '<</Type/Page>>hello');
        $document->setObjects(array('1_0' => $ref));

        $element = \Smalot\PdfParser\Element\ElementArray::parse(' [ 1 0 R ]', $document);
        /** @var \Smalot\PdfParser\Object $content */
        $content = current($element->getContent());
        $this->assert->object($content)->isInstanceOf('\Smalot\PdfParser\Object');
        $this->assert->string($content->getContent())->isEqualTo('hello');
    }

    public function test__toString()
    {
        $val_4   = new \Smalot\PdfParser\Element\ElementNumeric('4');
        $val_2   = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $element = new \Smalot\PdfParser\Element\ElementArray(array($val_4, $val_2));
        $this->assert->castToString($element)->isEqualTo('4,2');

        $document = new \Smalot\PdfParser\Document(array());
        $element  = \Smalot\PdfParser\Element\ElementArray::parse(' [ 4 2 ]', $document);
        $this->assert->castToString($element)->isEqualTo('4,2');
    }
}

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
 * Class ElementName
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementName extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('ABC', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' [ /ABC 5 6 ]', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' << invalid >>', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' / FlateDecode ', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' 0 /FlateDecode ', null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(" 0 \n /FlateDecode ", null, $offset);
        $this->boolean($element)->isEqualTo(false);
        $this->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' /FlateDecode ', null, $offset);
        $this->string($element->getContent())->isEqualTo('FlateDecode');
        $this->integer($offset)->isEqualTo(13);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(' /FlateDecode', null, $offset);
        $this->string($element->getContent())->isEqualTo('FlateDecode');
        $this->integer($offset)->isEqualTo(13);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/FlateDecode', null, $offset);
        $this->string($element->getContent())->isEqualTo('FlateDecode');
        $this->integer($offset)->isEqualTo(12);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse(" \n /FlateDecode ", null, $offset);
        $this->string($element->getContent())->isEqualTo('FlateDecode');
        $this->integer($offset)->isEqualTo(15);

        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/FlateDecode2', null, $offset);
        $this->string($element->getContent())->isEqualTo('FlateDecode2');
        $this->integer($offset)->isEqualTo(13);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/Flate-Decode2', null, $offset);
        $this->string($element->getContent())->isEqualTo('Flate-Decode2');
        $this->integer($offset)->isEqualTo(14);

        //
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/Flate_Decode2', null, $offset);
        $this->string($element->getContent())->isEqualTo('Flate');
        $this->integer($offset)->isEqualTo(6);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementName::parse('/Flate.Decode2', null, $offset);
        $this->string($element->getContent())->isEqualTo('Flate');
        $this->integer($offset)->isEqualTo(6);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode');
        $this->string($element->getContent())->isEqualTo('FlateDecode');
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode');
        $this->boolean($element->equals('FlateDecode'))->isEqualTo(true);
        $this->boolean($element->equals('Flatedecode'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode2');
        $this->boolean($element->equals('FlateDecode2'))->isEqualTo(true);
        $this->boolean($element->equals('FlateDecode3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementName('Flate-Decode2');
        $this->boolean($element->equals('Flate-Decode2'))->isEqualTo(true);
        $this->boolean($element->equals('Flate-Decode3'))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode');
        $this->boolean($element->contains('FlateDecode'))->isEqualTo(true);
        $this->boolean($element->contains('Flatedecode'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode2');
        $this->boolean($element->contains('FlateDecode2'))->isEqualTo(true);
        $this->boolean($element->contains('FlateDecode3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementName('Flate-Decode2');
        $this->boolean($element->contains('Flate-Decode2'))->isEqualTo(true);
        $this->boolean($element->contains('Flate-Decode3'))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementName('FlateDecode');
        $this->string($element->__toString())->isEqualTo('FlateDecode');
    }
}

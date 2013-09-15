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
 * Class ElementString
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementString extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse('ABC', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' [ (ABC) 5 6 ]', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' << (invalid) >>', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' / (FlateDecode) ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' 0 (FlateDecode) ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(" 0 \n (FlateDecode) ", null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' (Copyright) ', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
        $this->assert->integer($offset)->isEqualTo(12);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' (Copyright) ', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
        $this->assert->integer($offset)->isEqualTo(12);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(' (Copyright)', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
        $this->assert->integer($offset)->isEqualTo(12);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse('(Copyright)', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
        $this->assert->integer($offset)->isEqualTo(11);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse('(Copy-right2)', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copy-right2');
        $this->assert->integer($offset)->isEqualTo(13);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse(" \n (Copyright) ", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
        $this->assert->integer($offset)->isEqualTo(14);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse('()', null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('');
        $this->assert->integer($offset)->isEqualTo(2);

        // Complex study case : Unicode + octal.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse("(\xFE\xFF\\000M)", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo('M');
        $this->assert->integer($offset)->isEqualTo(9);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementString::parse("(<20>)", null, $offset);
        $this->assert->string($element->getContent())->isEqualTo(' ');
        $this->assert->integer($offset)->isEqualTo(6);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementString('Copyright');
        $this->assert->string($element->getContent())->isEqualTo('Copyright');
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementString('CopyRight');
        $this->assert->boolean($element->equals('CopyRight'))->isEqualTo(true);
        $this->assert->boolean($element->equals('Flatedecode'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementString('CopyRight2');
        $this->assert->boolean($element->equals('CopyRight2'))->isEqualTo(true);
        $this->assert->boolean($element->equals('CopyRight3'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementString('Flate-Decode2');
        $this->assert->boolean($element->equals('Flate-Decode2'))->isEqualTo(true);
        $this->assert->boolean($element->equals('Flate-Decode3'))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementString('CopyRight');
        $this->assert->boolean($element->contains('CopyRight'))->isEqualTo(true);
        $this->assert->boolean($element->contains('Copyright'))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementString('CopyRight2');
        $this->assert->boolean($element->contains('CopyRight2'))->isEqualTo(true);
        $this->assert->boolean($element->contains('CopyRight3'))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementString('CopyRight');
        $this->assert->castToString($element)->isEqualTo('CopyRight');
    }
}

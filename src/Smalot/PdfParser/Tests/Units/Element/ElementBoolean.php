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
 * Class ElementBoolean
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementBoolean extends atoum\test
{
    public function testParse()
    {
        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse('ABC', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' [ false ]', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' << true >>', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' / false ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' 0 true ', null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(" 0 \n true ", null, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' true ', null, $offset);
        $this->assert->boolean($element->getContent())->isEqualTo(true);
        $this->assert->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' TRUE ', null, $offset);
        $this->assert->boolean($element->getContent())->isEqualTo(true);
        $this->assert->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(' True', null, $offset);
        $this->assert->boolean($element->getContent())->isEqualTo(true);
        $this->assert->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse('true', null, $offset);
        $this->assert->boolean($element->getContent())->isEqualTo(true);
        $this->assert->integer($offset)->isEqualTo(4);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse('False', null, $offset);
        $this->assert->boolean($element->getContent())->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(5);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementBoolean::parse(" \n true ", null, $offset);
        $this->assert->boolean($element->getContent())->isEqualTo(true);
        $this->assert->integer($offset)->isEqualTo(7);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementBoolean('true');
        $this->assert->boolean($element->getContent())->isEqualTo(true);
        $element = new \Smalot\PdfParser\Element\ElementBoolean('false');
        $this->assert->boolean($element->getContent())->isEqualTo(false);
    }

    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementBoolean('true');
        $this->assert->boolean($element->equals(true))->isEqualTo(true);
        $this->assert->boolean($element->equals(1))->isEqualTo(false);
        $this->assert->boolean($element->equals(false))->isEqualTo(false);
        $this->assert->boolean($element->equals(null))->isEqualTo(false);

        $element = new \Smalot\PdfParser\Element\ElementBoolean('false');
        $this->assert->boolean($element->equals(false))->isEqualTo(true);
        $this->assert->boolean($element->equals(0))->isEqualTo(false);
        $this->assert->boolean($element->equals(true))->isEqualTo(false);
        $this->assert->boolean($element->equals(null))->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementBoolean('true');
        $this->assert->boolean($element->contains(true))->isEqualTo(true);
        $this->assert->boolean($element->contains(false))->isEqualTo(false);
        $this->assert->boolean($element->contains(1))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementBoolean('true');
        $this->assert->castToString($element)->isEqualTo('true');
        $element = new \Smalot\PdfParser\Element\ElementBoolean('false');
        $this->assert->castToString($element)->isEqualTo('false');
    }
}

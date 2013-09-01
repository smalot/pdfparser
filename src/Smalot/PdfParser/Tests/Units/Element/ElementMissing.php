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
 * Class ElementMissing
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementMissing extends atoum\test
{
    public function testEquals()
    {
        $element = new \Smalot\PdfParser\Element\ElementMissing(null);
        $this->assert->boolean($element->equals(null))->isEqualTo(false);
        $this->assert->boolean($element->equals(true))->isEqualTo(false);
        $this->assert->boolean($element->equals('A'))->isEqualTo(false);
        $this->assert->boolean($element->equals(false))->isEqualTo(false);
    }

    public function testGetContent()
    {
        $element = new \Smalot\PdfParser\Element\ElementMissing(null);
        $this->assert->boolean($element->getContent())->isEqualTo(false);
    }

    public function testContains()
    {
        $element = new \Smalot\PdfParser\Element\ElementMissing(null);
        $this->assert->boolean($element->contains(null))->isEqualTo(false);
        $this->assert->boolean($element->contains(true))->isEqualTo(false);
        $this->assert->boolean($element->contains('A'))->isEqualTo(false);
        $this->assert->boolean($element->contains(false))->isEqualTo(false);
    }

    public function test__toString()
    {
        $element = new \Smalot\PdfParser\Element\ElementMissing(null);
        $this->assert->castToString($element)->isEqualTo('');
    }
}

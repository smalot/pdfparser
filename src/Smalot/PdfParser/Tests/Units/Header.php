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

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Header
 * @package Smalot\PdfParser\Tests\Units
 */
class Header extends atoum\test
{
    public function testParse()
    {
        $document = new \Smalot\PdfParser\Document();

        $content  = '<</Type/Page/SubType/Text>>foo';
        $position = 0;
        $header   = \Smalot\PdfParser\Header::parse($content, $document, $position);

        $this->assert->object($header)->isInstanceOf('\Smalot\PdfParser\Header');
        $this->assert->integer($position)->isEqualTo(27);
        $this->assert->array($header->getElements())->hasSize(2);

        // No header to parse
        $this->assert->castToString($header->get('Type'))->isEqualTo('Page');
        $content  = 'foo';
        $position = 0;
        $header   = \Smalot\PdfParser\Header::parse($content, $document, $position);

        $this->assert->object($header)->isInstanceOf('\Smalot\PdfParser\Header');
        $this->assert->integer($position)->isEqualTo(0);
        $this->assert->array($header->getElements())->hasSize(0);

    }

    public function testGetElements()
    {
    }

    public function testHas()
    {
    }

    public function testGet()
    {
    }

    public function testResolveXRef()
    {
    }
}

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

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Filters
 *
 * @package Smalot\PdfParser\Tests\Units
 */
class Filters extends atoum\test
{
    public function testDecodeFilter()
    {
        // FlateDecode
        $compressed   = gzcompress('hello');
        $uncompressed = \Smalot\PdfParser\Filters::decodeFilter('FlateDecode', $compressed);
        $this->assert->string($uncompressed)->isEqualTo('hello');
    }
}

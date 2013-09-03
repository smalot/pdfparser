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
 * Class Object
 *
 * @package Smalot\PdfParser\Tests\Units
 */
class Object extends atoum\test
{
    public function testParse()
    {
        $content = <<<EOT
<< /Type /Page /Parent 3 0 R /Resources 6 0 R /Contents 4 0 R /MediaBox [0 0 595.32 841.92]
>>
main content
EOT;

        $document = new \Smalot\PdfParser\Document();
        $object   = \Smalot\PdfParser\Object::parse($document, $content);

//        var_dump($object);
    }
}

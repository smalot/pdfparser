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
        $this->assert->string($object->getContent())->isEqualTo('main content');
    }

    public function testGetTextParts()
    {
//        $content  = "T*[()-3.86158()-5.32638()]TJ
//()'
///R11 9 Tf
//0.999427 0 0 1 42.5995 418.281 Tm
//[()-3.6805()0.241838()-35.3258(\n)0.697829(	)-6.75192()0.697829()-5.7463()8.69965()-6.18682()-4.64133()-35.3262(1)3.56324(1)3.56324(1)3.56324(1)3.56406(1)3.56324(1)3.56324(1)3.56324(1)-9.77774(1)3.56324(1)3.56161(1)-9.77774(1)3.56324(1)3.56324(1)3.56324(1)-9.77774(1)3.56324(1)3.56324(1)-9.77774(1)3.56324(1)3.56324(1)3.56324(1)-9.77774(1)3.56487(1)3.56324(1)3.56324(1)3.56324(1)-9.77774(1)3.56324(1)3.56324(1)-9.77774(1)3.56324(1)3.56324(1)3.56324(1)-9.77774(1)3.56324(1)3.56324(1)-9.77774(1)3.56324(1)3.56324(1)]TJ
//179.863 0 Td";
//
//        $document = new \Smalot\PdfParser\Document();
//        $object   = new \Smalot\PdfParser\Object($document, null, 'BT' . $content . 'ET');
//
//        var_dump($object->getTextParts());
//
//        var_dump($object->getCommandsFromTextPart($content));
    }
}

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
 * Class Font
 *
 * @package Smalot\PdfParser\Tests\Units
 */
class Font extends atoum\test
{
    public function testLoadTranslateTable()
    {
        $document = new \Smalot\PdfParser\Document();

        $content  = '<</Type/Font /Subtype /Type0 /ToUnicode 2 0 R>>';
        $font     = \Smalot\PdfParser\Object::parse($document, $content);

        $content = '/CIDInit /ProcSet findresource begin
14 dict begin
begincmap
/CIDSystemInfo
<< /Registry (Adobe)
/Ordering (UCS)
/Supplement 0
>> def
/CMapName /Adobe-Identity-UCS def
/CMapType 2 def
1 begincodespacerange
<0000> <FFFF>
endcodespacerange
3 beginbfchar
<0003> <0020>
<000F> <002C>
<0011> <002E>
endbfchar
2 beginbfrange
<0013> <0016> <0030>
<0018> <001C> <0035>
endbfrange
7 beginbfchar
<0023> <0040>
<0026> <0043>
<0028> <0045>
<0030> <004D>
<0033> <0050>
<0035> <0052>
<0039> <0056>
endbfchar
4 beginbfrange
<0044> <004C> <0061>
<004F> <0052> <006C>
<0054> <0059> <0071>
<005B> <005C> <0078>
endbfrange
4 beginbfchar
<0070> <00E9>
<00AB> <2026>
<00B0> <0153>
<00B6> <2019>
endbfchar
endcmap
CMapName currentdict /CMap defineresource pop
end
end';
        $unicode   = new \Smalot\PdfParser\Object($document, null, $content);

        $document->setObjects(array(1 => $font, 2 => $unicode));

        $table = $font->loadTranslateTable();
        // Test reload
        $table = $font->loadTranslateTable();

        $this->assert->array($table)->hasSize(44);

        // Test chars
        $this->assert->string($table[3])->isEqualTo(' ');
        $this->assert->string($table[15])->isEqualTo(',');
        $this->assert->string($table[17])->isEqualTo('.');
        $this->assert->string($table[35])->isEqualTo('@');
        $this->assert->string($table[57])->isEqualTo('V');

        // Test ranges
        $this->assert->string($table[85])->isEqualTo('r');
        $this->assert->string($table[92])->isEqualTo('y');
    }
}

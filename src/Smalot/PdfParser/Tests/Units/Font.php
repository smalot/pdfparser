<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
 * @license LGPLv3
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2017 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 *
 */

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;
use Smalot\PdfParser\Header;

/**
 * Class Font
 *
 * @package Smalot\PdfParser\Tests\Units
 */
class Font extends atoum\test
{
    public function testGetName()
    {
        $filename = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser   = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $fonts    = $document->getFonts();
        $font     = reset($fonts);

        $this->assert->string($font->getName())->isEqualTo('OJHCYD+Cambria,Bold');
    }

    public function testGetType()
    {
        $filename = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser   = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $fonts    = $document->getFonts();
        $font     = reset($fonts);

        $this->assert->string($font->getType())->isEqualTo('TrueType');
    }

    public function testGetDetails()
    {
        $filename  = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser    = new \Smalot\PdfParser\Parser();
        $document  = $parser->parseFile($filename);
        $fonts     = $document->getFonts();
        $font      = reset($fonts);
        $reference = array(
            'Name'           => 'OJHCYD+Cambria,Bold',
            'Type'           => 'TrueType',
            'Encoding'       => 'Ansi',
            'BaseFont'       => 'OJHCYD+Cambria,Bold',
            'FontDescriptor' =>
            array(
                'Type'         => 'FontDescriptor',
                'FontName'     => 'OJHCYD+Cambria,Bold',
                'Flags'        => 4,
                'Ascent'       => 699,
                'CapHeight'    => 699,
                'Descent'      => -7,
                'ItalicAngle'  => 0,
                'StemV'        => 128,
                'MissingWidth' => 658,
            ),
            'ToUnicode'      =>
            array(
                'Filter' => 'FlateDecode',
                'Length' => 219,
            ),
            'FirstChar'      => 1,
            'LastChar'       => 11,
            'Widths'         =>
            array(
                0  => 705,
                1  => 569,
                2  => 469,
                3  => 597,
                4  => 890,
                5  => 531,
                6  => 604,
                7  => 365,
                8  => 220,
                9  => 314,
                10 => 308,
            ),
            'Subtype'        => 'TrueType',
        );
        $this->assert->array($font->getDetails())->isEqualTo($reference);
    }

    public function testTranslateChar()
    {
        $filename = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser   = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $fonts    = $document->getFonts();
        /** @var \Smalot\PdfParser\Font $font */
        $font = reset($fonts);

        $this->assert->string($font->translateChar("\x01"))->isEqualTo('D');
        $this->assert->string($font->translateChar("\x02"))->isEqualTo('o');
        $this->assert->string($font->translateChar("\x03"))->isEqualTo('c');
        $this->assert->string($font->translateChar("\x04"))->isEqualTo('u');
        $this->assert->string($font->translateChar("\x99"))->isEqualTo(\Smalot\PdfParser\Font::MISSING);
    }

    public function testLoadTranslateTable()
    {
        $document = new \Smalot\PdfParser\Document();

        $content = '<</Type/Font /Subtype /Type0 /ToUnicode 2 0 R>>';
        $header  = Header::parse($content, $document);
        $font    = new \Smalot\PdfParser\Font($document, $header);

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
1 beginbfrange
<0084> <0086> [<0061> <0071> <0081>]
endbfrange
endcmap
CMapName currentdict /CMap defineresource pop
end
end';
        $unicode = new \Smalot\PdfParser\PDFObject($document, null, $content);

        $document->setObjects(array('1_0' => $font, '2_0' => $unicode));

        $font->init();
        // Test reload
        $table = $font->loadTranslateTable();

        $this->assert->array($table)->hasSize(47);

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

    public function testDecodeHexadecimal()
    {
        $hexa = '<322041>';
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa))->isEqualTo("2 A");
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa, false))->isEqualTo("2 A");
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa, true))->isEqualTo("(2 A)");

        $hexa = '<003200200041>';
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa))->isEqualTo("\x002\x00 \x00A");
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa, false))->isEqualTo("\x002\x00 \x00A");
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa, true))->isEqualTo("(\x002\x00 \x00A)");

        $hexa = '<00320020> 8 <0041>';
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa))->isEqualTo("\x002\x00  8 \x00A");
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa, false))->isEqualTo("\x002\x00  8 \x00A");
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa, true))->isEqualTo(
            "(\x002\x00 ) 8 (\x00A)"
        );

        $hexa = '<3220> 8 <41>';
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa))->isEqualTo("2  8 A");
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa, false))->isEqualTo("2  8 A");
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa, true))->isEqualTo("(2 ) 8 (A)");

        $hexa = '<00320020005C>-10<0041>';
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa))->isEqualTo("\x002\x00 \x00\\-10\x00A");
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa, false))->isEqualTo(
            "\x002\x00 \x00\\-10\x00A"
        );
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa, true))->isEqualTo(
            "(\x002\x00 \x00\\\\)-10(\x00A)"
        );

        // If it contents XML, the function need to return the same value.
        $hexa = '<?xml version="1.0"?><body xmlns="http://www.w3.org/1999/xhtml" xmlns:xfa="http://www.xfa.org/schema/xfa-data/1.0/" xfa:APIVersion="Acrobat:19.10.0" xfa:spec="2.0.2"  style="font-size:12.0pt;text-align:left;color: 0000;font-weight:normal;font-style:norm\
al;font-family:Helvetica,sans-serif;font-stretch:normal"><p><span style="font-family:Helvetica">Example</span></p></body>';
        $this->assert->string(\Smalot\PdfParser\Font::decodeHexadecimal($hexa))->isEqualTo($hexa);
    }

    public function testDecodeOctal()
    {
        $this->assert->string(\Smalot\PdfParser\Font::decodeOctal("\\101\\102\\040\\103"))->isEqualTo('AB C');
        $this->assert->string(\Smalot\PdfParser\Font::decodeOctal("\\101\\102\\040\\103D"))->isEqualTo('AB CD');
    }

    public function testDecodeEntities()
    {
        $this->assert->string(\Smalot\PdfParser\Font::decodeEntities("File#20Type"))->isEqualTo('File Type');
        $this->assert->string(\Smalot\PdfParser\Font::decodeEntities("File##20Ty#pe"))->isEqualTo('File# Ty#pe');
    }

    public function testDecodeUnicode()
    {
        $this->assert->string(\Smalot\PdfParser\Font::decodeUnicode("\xFE\xFF\x00A\x00B"))->isEqualTo('AB');
    }

    public function testDecodeText()
    {
        $filename = __DIR__ . '/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser   = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $fonts    = $document->getFonts();
        /** @var \Smalot\PdfParser\Font $font */
        // Cambria
        $font     = reset($fonts);
        $commands = array(
            array(
                't' => '',
                'c' => "\x01\x02",
            ),
            array(
                't' => 'n',
                'c' => -10,
            ),
            array(
                't' => '',
                'c' => "\x03",
            ),
            array(
                't' => '',
                'c' => "\x04",
            ),
            array(
                't' => 'n',
                'c' => -100,
            ),
            array(
                't' => '<',
                'c' => "01020304",
            ),
        );
        $this->assert->string($font->decodeText($commands))->isEqualTo('Docu Docu');

        //Check if ANSI/Unicode detection is working properly
        $filename = __DIR__ . '/../../../../../samples/bugs/Issue95_ANSI.pdf';
        $parser   = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $fonts    = $document->getFonts();
        /** @var \Smalot\PdfParser\Font $font */
        $font     = reset($fonts);
        $commands = array(
            array(
                't' => '<',
                'c' => "E6F6FC", //ANSI encoded string
            ),
        );
        $this->assert->string($font->decodeText($commands))->isEqualTo('æöü');

        $commands = array(
            array(
                't' => '<',
                'c' => "C3A6C3B6C3BC", //Unicode encoded string
            ),
        );
        $this->assert->string($font->decodeText($commands))->isEqualTo('æöü');
    }

    public function testXmlContent()
    {
        $filename = __DIR__ . '/../../../../../samples/bugs/Issue18.pdf';
        $parser   = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $text = trim($pages[0]->getText());

        $this->assert->string($text)->isEqualTo('Example PDF');
    }
}

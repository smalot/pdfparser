<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-3.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2014 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 *
 */

// Source : http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/WinANSIEncoding.pm

namespace Smalot\PdfParser\Encoding;

/**
 * Class WinAnsiEncoding
 *
 * @package Smalot\PdfParser\Encoding
 */
class WinAnsiEncoding implements EncodingInterface
{
    public function getTranslations()
    {
        $encoding =
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef ' .
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef ' .
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef ' .
          '.notdef .notdef .notdef .notdef .notdef .notdef .notdef .notdef ' .
          'space exclam quotedbl numbersign dollar percent ampersand quotesingle ' .
          'parenleft parenright asterisk plus comma hyphen period slash zero one ' .
          'two three four five six seven eight nine colon semicolon less equal ' .
          'greater question at A B C D E F G H I J K L M N O P Q R S T U V W X ' .
          'Y Z bracketleft backslash bracketright asciicircum underscore ' .
          'grave a b c d e f g h i j k l m n o p q r s t u v w x y z ' .
          'braceleft bar braceright asciitilde bullet Euro bullet quotesinglbase ' .
          'florin quotedblbase ellipsis dagger daggerdbl circumflex perthousand ' .
          'Scaron guilsinglleft OE bullet Zcaron bullet bullet quoteleft quoteright ' .
          'quotedblleft quotedblright bullet endash emdash tilde trademark scaron ' .
          'guilsinglright oe bullet zcaron Ydieresis space exclamdown cent ' .
          'sterling currency yen brokenbar section dieresis copyright ' .
          'ordfeminine guillemotleft logicalnot hyphen registered macron degree ' .
          'plusminus twosuperior threesuperior acute mu paragraph ' .
          'periodcentered cedilla onesuperior ordmasculine guillemotright ' .
          'onequarter onehalf threequarters questiondown Agrave Aacute ' .
          'Acircumflex Atilde Adieresis Aring AE Ccedilla Egrave Eacute ' .
          'Ecircumflex Edieresis Igrave Iacute Icircumflex Idieresis Eth Ntilde ' .
          'Ograve Oacute Ocircumflex Otilde Odieresis multiply Oslash Ugrave ' .
          'Uacute Ucircumflex Udieresis Yacute Thorn germandbls agrave aacute ' .
          'acircumflex atilde adieresis aring ae ccedilla egrave eacute ' .
          'ecircumflex edieresis igrave iacute icircumflex idieresis eth ntilde ' .
          'ograve oacute ocircumflex otilde odieresis divide oslash ugrave ' .
          'uacute ucircumflex udieresis yacute thorn ydieresis';

        return explode(' ', $encoding);
    }
}

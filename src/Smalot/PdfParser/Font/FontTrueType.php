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

namespace Smalot\PdfParser\Font;

use Smalot\PdfParser\Font;

/**
 * Class FontTrueType
 * @package Smalot\PdfParser\Font
 */
class FontTrueType extends Font
{
    public function translateChar($char, $is_hexa = false)
    {
        $this->init();

        //echo 'translate char (font): ' . $char;

        if ($is_hexa) {
            $char = str_pad($char, 4, '0', STR_PAD_RIGHT);
            $dec  = hexdec($char);
        } else {
            $dec = hexdec(bin2hex($char));
        }

//        echo ' (dec: ' . $dec . ")";

        if (array_key_exists($dec, $this->table['chars'])) {
//            echo ' => ' . $this->table['chars'][$dec] . "\n";
            return $this->table['chars'][$dec];
        } else {
            if (is_string($this->encoding)) {
                switch ($this->encoding) {
                    case 'MacRomanEncoding':
                        $new_char = @iconv('MacRoman', 'UTF-8', $char);
                        break;

                    default:
                        $new_char = $char;
                }

                return ($this->table['chars'][$dec] = $new_char);
            } elseif ($this->encoding instanceof Encoding) {
                //var_dump($this->encoding);
                $new_dec = $this->encoding->translateChar($dec);
                //$new_char = html_entity_decode('&#' . $new_dec . ';', ENT_NOQUOTES, 'UTF-8');
                $new_char = $new_dec;

                //echo ' => ' . $new_char . ' (dec: ' . $new_dec . ")\n";

                return ($this->table['chars'][$dec] = $new_char);
            } else {
                return html_entity_decode('&#' . $dec . ';', ENT_NOQUOTES, 'UTF-8');
            }
        }
    }
}

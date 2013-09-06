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

namespace Smalot\PdfParser\Font;

use Smalot\PdfParser\Encoding;
use Smalot\PdfParser\Font;

/**
 * Class FontTrueType
 *
 * @package Smalot\PdfParser\Font
 */
class FontTrueType extends Font
{
    /**
     *
     */
    /*public function loadTranslateTable()
    {
        parent::loadTranslateTable();

        if ($this->has('Encoding')) {
            $encoding = $this->get('Encoding');

            if ($encoding->has('Differences')) {
                echo 'has Differences';
                $firstChar   = $this->get('FirstChar')->getContent();
                $lastChar    = $this->get('LastChar')->getContent();
                $differences = $encoding->get('Differences')->getContent();
            }

            foreach ($differences as $position => $difference) {
                echo 'check for ' . $difference . "\n";
                $this->table[$firstChar + $position] = $encoding->convertTokenToInt($difference);
            }

            var_dump($this->table);
        }

        if (!is_null($this->table)) {
            return;
        }

        $this->table = array(
            'chars'  => array(),
            'ranges' => array(),
        );

        if (!$this->getToUnicode() instanceof ElementMissing) {
            $content = $this->getToUnicode()->getContent();

            if (strpos($content, 'beginbfchar') !== false) {
                $bfchar  = substr(
                    $content,
                    strpos($content, 'beginbfchar') + strlen('beginbfchar'),
                    strpos($content, 'endbfchar') - strpos($content, 'beginbfchar') - strlen('beginbfchar')
                );
                $regexp  = '/<(?<from>[0-9A-Z]+)> +<(?<to>[0-9A-Z]+)>[ \r\n]+/ims';
                $matches = array();
                preg_match_all($regexp, $bfchar, $matches);
                foreach ($matches['from'] as $key => $from) {
                    $to = $matches['to'][$key];
                    $to = self::uchr(hexdec($to));
                    $this->table[hexdec($from)] = $to;
                }
            }

            if (strpos($content, 'beginbfrange') !== false) {
                $bfrange = substr(
                    $content,
                    strpos($content, 'beginbfrange') + strlen('beginbfrange'),
                    strpos($content, 'endbfrange') - strpos($content, 'beginbfrange') - strlen('beginbfrange')
                );
                $regexp  = '/<(?<from>[0-9A-Z]+)> *<(?<to>[0-9A-Z]+)> *<(?<offset>[0-9A-Z]+)>[ \r\n]+/ims';
                $matches = array();
                preg_match_all($regexp, $bfrange, $matches);
                foreach ($matches['from'] as $key => $from) {
                    $char_from = hexdec($from);
                    $char_to   = hexdec($matches['to'][$key]);
                    $offset    = hexdec($matches['offset'][$key]);

                    $this->table['ranges'][] = array(
                        'from'   => $char_from,
                        'to'     => $char_to,
                        'offset' => $offset,
                    );

                    for ($char = $char_from; $char <= $char_to; $char++) {
                        $to = self::uchr($char - $char_from + $offset);

                        $this->table[$char] = $to;
                    }
                }
            }
        }
    }*/

    /**
     * @param string $char
     * @param bool   $is_hexa
     *
     * @return string
     */
//    public function translateChar($char, $is_hexa = false)
//    {
//        $this->init();
//
//        if ($is_hexa) {
//            $char = str_pad($char, 4, '0', STR_PAD_RIGHT);
//            $dec  = hexdec($char);
//        } else {
//            $dec = hexdec(bin2hex($char));
//        }
//
//        if (array_key_exists($dec, $this->table)) {
//            return $this->table[$dec];
//        } else {
//            if (is_string($this->encoding)) {
//                switch ($this->encoding) {
//                    case 'MacRomanEncoding':
//                        $new_char = @iconv('MacRoman', 'UTF-8', $char);
//                        break;
//
//                    default:
//                        $new_char = $char;
//                }
//
//                return ($this->table[$dec] = $new_char);
//            } elseif ($this->encoding instanceof Encoding) {
//                die('test');
//                $new_dec  = $this->encoding->translateChar($dec);
//                $new_char = $new_dec;
//
//                return ($this->table[$dec] = $new_char);
//            } else {
//                return self::uchr($dec);
//            }
//        }
//    }
}

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

namespace Smalot\PdfParser;

use Smalot\PdfParser\Element\ElementMissing;

/**
 * Class Font
 *
 * @package Smalot\PdfParser
 */
class Font extends Object
{
    /**
     * @var Object
     */
    protected $toUnicode = false;

    /**
     * @var array
     */
    protected $table = null;

    /**
     * @var mixed
     */
    protected $encoding = null;

    /**
     *
     */
    public function init()
    {
        // Load encoding informations.
        $encoding = $this->get('Encoding');

        $this->encoding = $encoding;

        // Load translate table.
        $this->loadTranslateTable();
    }

    /**
     * @return null|Object
     */
    public function getToUnicode()
    {
        if ($this->toUnicode !== false) {
            return $this->toUnicode;
        }

        $toUnicode = $this->get('ToUnicode');

        return ($this->toUnicode = $toUnicode);
    }

    /**
     * @param string $char
     *
     * @return string
     */
    public function translateChar($char)
    {
        $dec = hexdec(bin2hex($char));

//        echo '<<<<<<<<<<<<<<<' . "\n";
//        var_dump($char, bin2hex($char), $dec);

        if (array_key_exists($dec, $this->table)) {
            $char = $this->table[$dec];
//            var_dump($char);
        } else {
//            var_dump('unknown');
        }

//        echo '>>>>>>>>>>>>>>>' . "\n";

        return $char;

    }

    /**
     * @param int $code
     *
     * @return string
     */
    protected static function uchr($code)
    {
        return html_entity_decode('&#' . ((int)$code) . ';', ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * @return array
     */
    public function loadTranslateTable()
    {
        if (!is_null($this->table)) {
            return $this->table;
        }

        $this->table = array();

        if ($this->getToUnicode() instanceof Object || true) {
            $content = $this->getToUnicode()->getContent();
            $matches = array();

            // Support for multiple bfchar sections
            if (preg_match_all('/beginbfchar(?<sections>.*?)endbfchar/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
                    $regexp  = '/<(?<from>[0-9A-F]+)> +<(?<to>[0-9A-F]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    foreach ($matches['from'] as $key => $from) {
                        $to = $matches['to'][$key];
                        $to = self::uchr(hexdec($to));
                        $this->table[hexdec($from)] = $to;
//                        var_dump($from, hexdec($from), $to);
//                        echo "1---------------\n";
                    }
                }
            }

            // Support for multiple bfrange sections
            if (preg_match_all('/beginbfrange(?<sections>.*?)endbfrange/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
                    // Support for : <srcCode1> <srcCode2> <dstString>
                    $regexp  = '/<(?<from>[0-9A-F]+)> *<(?<to>[0-9A-F]+)> *<(?<offset>[0-9A-F]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    foreach ($matches['from'] as $key => $from) {
                        $char_from = hexdec($from);
                        $char_to   = hexdec($matches['to'][$key]);
                        $offset    = hexdec($matches['offset'][$key]);

                        for ($char = $char_from; $char <= $char_to; $char++) {
                            $this->table[$char] = self::uchr($char - $char_from + $offset);
//                            var_dump(dechex($char), $char, $this->table[$char]);
//                            echo "2---------------\n";
                        }
                    }

                    // Support for : <srcCode1> <srcCodeN> [<dstString1> <dstString2> ... <dstStringN>]
                    $regexp  = '/<(?<from>[0-9A-F]+)> *<(?<to>[0-9A-F]+)> *\[(?<strings>[<>0-9A-F ]+)\][ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

//                    var_dump($matches['strings']);
                    foreach ($matches['from'] as $key => $from) {
                        $char_from = hexdec($from);
                        $char_to   = hexdec($matches['to'][$key]);
                        $strings   = array();

                        preg_match_all('/(?<string><[0-9A-F]+>) */is', $matches['strings'][$key], $strings);

                        foreach ($strings['string'] as $position => $string) {
                            $this->table[$char_from + $position] = Font::decodeHexadecimal($string);
//                            var_dump($char_from + $position, $string, $this->table[$char_from + $position]);
//                            echo "3---------------\n";
                        }
                    }
//                    var_dump($matches);
                }
            }
        }

        return $this->table;
    }

    /**
     * @param string $hexa
     * @param bool   $add_braces
     *
     * @return string
     */
    public static function decodeHexadecimal($hexa, $add_braces = false)
    {
        $text  = '';
        $parts = preg_split('/(<[a-z0-9]+>)/si', $hexa, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match('/^<.*>$/', $part)) {
                $part = trim($part, '<>');
                if ($add_braces) {
                    $text .= '(';
                }

                for ($i = 0; $i < strlen($part); $i = $i + 4) {
                    $char = Font::uchr(hexdec(substr($part, $i, 4)));
//                    echo 'hexa: <' . substr($part, $i, 4) . '> : "' . $char . "\"\n";
                    $text .= ($add_braces?preg_replace('/\\\/s', '\\\\\\', $char):$char);
                }

                if ($add_braces) {
                    $text .= ')';
                }
            } else {
                $text .= $part;
            }
        }

//        var_dump($text);

        return $text;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public static function decodeOctal($text)
    {
        $matches = array();

        preg_match_all('/\\\\([0-9]{3})/s', $text, $matches);

        foreach ($matches[0] as $value) {
            $octal = substr($value, 1);
            $text  = self::uchr(octdec($octal));
        }

        return $text;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function decodeText($text)
    {
        $text          = self::decodeOctal($text);
        $cur_start_pos = 0;
        $word_position = 0;
        $words         = array();

        while (($cur_start_text = mb_strpos($text, '(', $cur_start_pos)) !== false) {
            // New text element found
            if ($cur_start_text - $cur_start_pos > 8) {
                ;
            } else {
                $spacing_size = floatval(trim(mb_substr($text, $cur_start_pos, $cur_start_text - $cur_start_pos)));

                // TODO : use matrix to determine spacing
                if ($spacing_size < -50) {
                    //$word_position++;
                }
            }

            $cur_start_text++;
            $start_search_end = $cur_start_text;

            while (($cur_start_pos = mb_strpos($text, ')', $start_search_end)) !== false) {
                $cur_extract = mb_substr($text, $cur_start_text, $cur_start_pos - $cur_start_text);
                preg_match('/(?<escape>[\\\]*)$/s', $cur_extract, $match);

                if (!(mb_strlen($match['escape']) % 2)) {
                    break;
                }

                $start_search_end = $cur_start_pos + 1;
            }

            // something wrong happened
            if ($cur_start_pos === false) {
                break;
            }

            // extract content
            $sub_text = mb_substr($text, $cur_start_text, $cur_start_pos - $cur_start_text);
            $sub_text = str_replace(
                array('\\\\', '\(', '\)', '\n', '\r', '\t'),
                array('\\',   '(',  ')',  "\n", "\r", "\t"),
                $sub_text
            );

            // add content to result string
            if (isset($words[$word_position])) {
                $words[$word_position] .= $sub_text;
            } else {
                $words[$word_position] = $sub_text;
            }

            $cur_start_pos++;
        }

        foreach ($words as &$word) {
//            echo 'before decode: "' . $word . '"' . "\n";
            $word = $this->decodeContent($word);
//            echo 'after decode : "' . $word . '"' . "\n";
//            echo "-----------------------------------\n";
        }

        return implode(' ', $words);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    protected function decodeContent($text)
    {
        if ($this->encoding instanceof Encoding) {

            $chars  = preg_split('//s', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $result = '';

            foreach ($chars as $char) {
                $dec_av = hexdec(bin2hex($char));
                $dec_ap = $this->encoding->translateChar($dec_av);
                $result .= self::uchr($dec_ap);
            }

            $text = $result;
        }

        if ($this->has('ToUnicode')) {
//            var_dump($this->get('ToUnicode')->getContent());
//            die();

            $chars  = preg_split('//us', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $result = '';

            foreach ($chars as $char) {
                $result .= $this->translateChar($char);
            }

            $text = $result;
        } else {
//            echo 'iconv convert CP1252 => UTF-8' . "\n";
            $text = @iconv('Windows-1252', 'UTF-8//TRANSLIT//IGNORE', $text);
        }

        return $text;
    }
}

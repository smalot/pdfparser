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
     * @var bool
     */
    protected $init_done = false;

    /**
     *
     */
    protected function init()
    {
        if (!$this->init_done) {
            // Load encoding informations.
            $encoding = $this->get('Encoding');

            $this->encoding = $encoding;

            // Load translate table.
            $this->loadTranslateTable();

            $this->init_done = true;
        }
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
    /*public function translateChar($char, $is_hexa = false)
    {
        $this->init();

        if ($is_hexa) {
            $char = str_pad($char, 4, '0', STR_PAD_RIGHT);
            $dec  = hexdec($char);
        } else {
            $dec = hexdec(bin2hex($char));
        }

        if (array_key_exists($dec, $this->table)) {
            return $this->table[$dec];
        } else {
            return $char;
        }
    }*/
    public function translateChar($char, $is_hexa = false)
    {
        $this->init();

        if ($is_hexa) {
            $char = str_pad($char, 4, '0', STR_PAD_RIGHT);
            $dec  = hexdec($char);
        } else {
            $dec = hexdec(bin2hex($char));
        }

        if (array_key_exists($dec, $this->table)) {
            return $this->table[$dec];
        } else {
            if (is_string($this->encoding)) {
                switch ($this->encoding) {
                    case 'MacRomanEncoding':
                        $new_char = @iconv('MacRoman', 'UTF-8', $char);
                        break;

                    default:
                        $new_char = $char;
                }

                return ($this->table[$dec] = $new_char);
            } elseif ($this->encoding instanceof Encoding) {
                die('test');
                $new_dec  = $this->encoding->translateChar($dec);
                $new_char = $new_dec;

                return ($this->table[$dec] = $new_char);
            } else {
                return $this->uchr($dec);
            }
        }
    }

    /**
     * @param int $code
     *
     * @return string
     */
    protected function uchr($code)
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
                    $regexp  = '/<(?<from>[0-9A-Z]+)> +<(?<to>[0-9A-Z]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    foreach ($matches['from'] as $key => $from) {
                        $to = $matches['to'][$key];
                        $to = $this->uchr(hexdec($to));
                        $this->table[hexdec($from)] = $to;
                    }
                }
            }

            // Support for multiple bfrange sections
            if (preg_match_all('/beginbfrange(?<sections>.*?)endbfrange/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
                    $regexp  = '/<(?<from>[0-9A-Z]+)> *<(?<to>[0-9A-Z]+)> *<(?<offset>[0-9A-Z]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    foreach ($matches['from'] as $key => $from) {
                        $char_from = hexdec($from);
                        $char_to   = hexdec($matches['to'][$key]);
                        $offset    = hexdec($matches['offset'][$key]);

                        for ($char = $char_from; $char <= $char_to; $char++) {
                            $to = $this->uchr($char - $char_from + $offset);

                            $this->table[$char] = $to;
                        }
                    }
                }
            }
        }

        return $this->table;
    }

    /**
     * @param string $hexa
     *
     * @return string
     */
    public function decodeHexadecimal($hexa)
    {
        $text = '';

        $parts = preg_split('/(<[a-z0-9]+>)/si', $hexa, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match('/^<.*>$/', $part)) {
                $part = trim($part, '<>');

                $text .= '(';
                for ($i = 0; $i < strlen($part); $i = $i + 4) {
                    echo 'hexa: ' . substr($part, $i, 4) . "\n";
                    $text .= pack('H*', ltrim(substr($part, $i, 4), '0'));
                }
                $text .= ')';
            } else {
                $text .= $part;
            }
        }

        return $text;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function decodeOctal($text)
    {
        $matches = array();

        preg_match_all('/\\\\([0-9]{3})/s', $text, $matches);

        foreach ($matches[0] as $value) {
            $octal = substr($value, 1);
            $text  = str_replace($value, chr(octdec($octal)), $text);
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
        $this->init();

        $text          = $this->decodeOctal($text);
        $result        = '';
        $cur_start_pos = 0;

        while (($cur_start_text = mb_strpos($text, '(', $cur_start_pos)) !== false) {
            // New text element found
            if ($cur_start_text - $cur_start_pos > 8) {
                $spacing = '';
            } else {
                $spacing_size = floatval(trim(mb_substr($text, $cur_start_pos, $cur_start_text - $cur_start_pos)));

                // TODO : use matrix to determine spacing
                if ($spacing_size < -50) {
                    $spacing = ' ';
                } else {
                    $spacing = '';
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
//            var_dump('avant', $sub_text);
            $sub_text = str_replace(
                array('\\\\', '\(', '\)', '\n', '\r'),
                array('\\', '(', ')', "\n", "\r"),
                $sub_text
            );

            // decode content
//                var_dump('decode');
            $sub_text = $this->decodeContent($sub_text);
//            var_dump('apres', $sub_text);
//            echo '--------------' . "\n";
//            var_dump('ajout espace', $spacing, $spacing_size);

            // add content to result string
            $result .= $spacing . $sub_text;
            $cur_start_pos++;
        }

        return $result;
    }

    protected function decodeContent($text)
    {
        $this->init();

        if ($this->encoding instanceof Encoding) {

            $chars  = preg_split('//', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $result = '';

            foreach ($chars as $char) {
                $dec_av = hexdec(bin2hex($char));
                $dec_ap = $this->encoding->translateChar($dec_av);
                $result .= $this->uchr($dec_ap);
            }

            return $result;
        }

        if (!$this->getToUnicode() instanceof ElementMissing) {
//            var_dump('font->translateChar');
//            die();

            $chars  = preg_split('//', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $result = '';

            foreach ($chars as $char) {
                $result .= $this->translateChar($char);
            }

            return $result;
        }


        return $text;
    }
}

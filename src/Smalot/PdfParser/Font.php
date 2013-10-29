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

use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementMissing;
use Smalot\PdfParser\Element\ElementXRef;

/**
 * Class Font
 *
 * @package Smalot\PdfParser
 */
class Font extends Object
{
    /**
     *
     */
    const MISSING = '?';

    /**
     * @var array
     */
    protected $table = null;

    /**
     * @var array
     */
    protected $tableSizes = null;

    /**
     *
     */
    public function init()
    {
        // Load translate table.
        $this->loadTranslateTable();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->has('BaseFont') ? (string)$this->get('BaseFont') : '[Unknown]';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return (string)$this->header->get('Subtype');
    }

    /**
     * @return array
     */
    public function getDetails($deep = true)
    {
        $details = array();

        $details['Name']     = $this->getName();
        $details['Type']     = $this->getType();
        $details['Encoding'] = ($this->has('Encoding') ? (string)$this->get('Encoding') : 'Ansi');

        $details += parent::getDetails($deep);

        return $details;
    }

    /**
     * @param string $char
     * @param bool   $use_default
     *
     * @return string
     */
    public function translateChar($char, $use_default = true)
    {
        $dec = hexdec(bin2hex($char));

        if (array_key_exists($dec, $this->table)) {
            $char = $this->table[$dec];
        } else {
            $char = ($use_default ? self::MISSING : false);
        }

        return $char;
    }

    /**
     * @param int $code
     *
     * @return string
     */
    public static function uchr($code)
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

        $this->table      = array();
        $this->tableSizes = array(
            'from' => 1,
            'to'   => 1,
        );

        if ($this->has('ToUnicode')) {
            $content = $this->get('ToUnicode')->getContent();
            $matches = array();

            // Support for multiple spacerange sections
            if (preg_match_all('/begincodespacerange(?<sections>.*?)endcodespacerange/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
                    $regexp = '/<(?<from>[0-9A-F]+)> *<(?<to>[0-9A-F]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    $this->tableSizes = array(
                        'from' => max(1, strlen(current($matches['from'])) / 2),
                        'to'   => max(1, strlen(current($matches['to'])) / 2),
                    );

                    break;
                }
            }

            // Support for multiple bfchar sections
            if (preg_match_all('/beginbfchar(?<sections>.*?)endbfchar/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
                    $regexp = '/<(?<from>[0-9A-F]+)> +<(?<to>[0-9A-F]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    $this->tableSizes['from'] = max(1, strlen(current($matches['from'])) / 2);

                    foreach ($matches['from'] as $key => $from) {
                        $parts = preg_split(
                            '/([0-9A-F]{4})/i',
                            $matches['to'][$key],
                            0,
                            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                        );
                        $text  = '';
                        foreach ($parts as $part) {
                            $text .= self::uchr(hexdec($part));
                        }
                        $this->table[hexdec($from)] = $text;
                    }
                }
            }

            // Support for multiple bfrange sections
            if (preg_match_all('/beginbfrange(?<sections>.*?)endbfrange/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
                    // Support for : <srcCode1> <srcCode2> <dstString>
                    $regexp = '/<(?<from>[0-9A-F]+)> *<(?<to>[0-9A-F]+)> *<(?<offset>[0-9A-F]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    foreach ($matches['from'] as $key => $from) {
                        $char_from = hexdec($from);
                        $char_to   = hexdec($matches['to'][$key]);
                        $offset    = hexdec($matches['offset'][$key]);

                        for ($char = $char_from; $char <= $char_to; $char++) {
                            $this->table[$char] = self::uchr($char - $char_from + $offset);
                        }
                    }

                    // Support for : <srcCode1> <srcCodeN> [<dstString1> <dstString2> ... <dstStringN>]
                    $regexp = '/<(?<from>[0-9A-F]+)> *<(?<to>[0-9A-F]+)> *\[(?<strings>[<>0-9A-F ]+)\][ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    foreach ($matches['from'] as $key => $from) {
                        $char_from = hexdec($from);
                        $strings   = array();

                        preg_match_all('/<(?<string>[0-9A-F]+)> */is', $matches['strings'][$key], $strings);

                        foreach ($strings['string'] as $position => $string) {
                            $parts = preg_split(
                                '/([0-9A-F]{4})/i',
                                $string,
                                0,
                                PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                            );
                            $text  = '';
                            foreach ($parts as $part) {
                                $text .= self::uchr(hexdec($part));
                            }
                            $this->table[$char_from + $position] = $text;
                        }
                    }
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

                $part = pack("H*", $part);
                $text .= ($add_braces ? preg_replace('/\\\/s', '\\\\\\', $part) : $part);

                if ($add_braces) {
                    $text .= ')';
                }
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
    public static function decodeOctal($text)
    {
        $parts = preg_split('/(\\\\\d{3})/s', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $text  = '';

        foreach ($parts as $part) {
            if (preg_match('/^\\\\\d{3}$/', $part)) {
                $text .= chr(octdec(trim($part, '\\')));
            } else {
                $text .= $part;
            }
        }

        return $text;
    }

    /**
     * @param $text
     *
     * @return string
     */
    public static function decodeEntities($text)
    {
        $parts = preg_split('/(#\d{2})/s', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $text  = '';

        foreach ($parts as $part) {
            if (preg_match('/^#\d{2}$/', $part)) {
                $text .= chr(hexdec(trim($part, '#')));
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
    public static function decodeUnicode($text)
    {
        if (preg_match('/^\xFE\xFF/i', $text)) {
            // Strip U+FEFF byte order marker.
            $decode = substr($text, 2);
            $text   = '';
            $length = strlen($decode);

            for ($i = 0; $i < $length; $i += 2) {
                $text .= self::uchr(hexdec(bin2hex(substr($decode, $i, 2))));
            }
        }

        return $text;
    }

    /**
     * @return int
     */
    protected function getFontSpaceLimit()
    {
        return -50;
    }

    /**
     * @param array $commands
     *
     * @return string
     */
    public function decodeText($commands)
    {
        $word_position = 0;
        $words         = array();
        $unicode       = false;
        $font_space    = $this->getFontSpaceLimit();

        foreach ($commands as $command) {
            switch ($command[Object::TYPE]) {
                case 'n':
                    if (floatval(trim($command[Object::COMMAND])) < $font_space) {
                        $word_position = count($words);
                    }
                    continue(2);

                case '<':
                    // Decode hexadecimal.
                    $text = self::decodeHexadecimal('<' . $command[Object::COMMAND] . '>');
                    $unicode = true;
                    break;

                default:
                    // Decode octal (if necessary).
                    $text = self::decodeOctal($command[Object::COMMAND]);
            }

            // replace escaped chars
            $text = str_replace(
                array('\\\\', '\(', '\)', '\n', '\r', '\t'),
                array('\\', '(', ')', "\n", "\r", "\t"),
                $text
            );

            // add content to result string
            if (isset($words[$word_position])) {
                $words[$word_position] .= $text;
            } else {
                $words[$word_position] = $text;
            }
        }

        foreach ($words as &$word) {
            $loop_unicode = $unicode;
            $word         = $this->decodeContent($word, $loop_unicode);
        }

        return implode(' ', $words);
    }

    /**
     * @param string $text
     * @param bool   $unicode
     *
     * @return string
     */
    protected function decodeContent($text, &$unicode)
    {
        if ($this->has('ToUnicode')) {

            $bytes = $this->tableSizes['from'];

            if ($bytes) {
                $result = '';
                $length = strlen($text);

                for ($i = 0; $i < $length; $i += $bytes) {
                    $char = substr($text, $i, $bytes);

                    if (($decoded = $this->translateChar($char, false)) !== false) {
                        $char = $decoded;
                    } elseif ($this->has('DescendantFonts')) {
                        $fonts   = $this->get('DescendantFonts')->getHeader()->getElements();
                        $decoded = false;

                        foreach ($fonts as $font) {
                            if ($font instanceof Font) {
                                if (($decoded = $font->translateChar($char, false)) !== false) {
                                    $decoded = @iconv('Windows-1252', 'UTF-8//TRANSLIT//IGNORE', $decoded);
                                    break;
                                }
                            }
                        }

                        if ($decoded !== false) {
                            $char = $decoded;
                        } else {
                            $char = @iconv('Windows-1252', 'UTF-8//TRANSLIT//IGNORE', $char);
                        }
                    } else {
                        $char = self::MISSING;
                    }

                    $result .= $char;
                }

                $text = $result;

                // By definition, this code generates unicode chars.
                $unicode = true;
            }
        } elseif ($this->has('Encoding')) {
            /** @var Encoding $encoding */
            $encoding = $this->get('Encoding');

            if ($encoding instanceof Encoding) {
                if ($unicode) {
                    $chars  = preg_split(
                        '//s' . ($unicode ? 'u' : ''),
                        $text,
                        -1,
                        PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
                    );
                    $result = '';

                    foreach ($chars as $char) {
                        $dec_av = hexdec(bin2hex($char));
                        $dec_ap = $encoding->translateChar($dec_av);
                        $result .= self::uchr($dec_ap);
                    }

                    $text = $result;
                } else {
                    $result = '';
                    $length = strlen($text);

                    for ($i = 0; $i < $length; $i++) {
                        $dec_av = hexdec(bin2hex($text[$i]));
                        $dec_ap = $encoding->translateChar($dec_av);
                        $result .= chr($dec_ap);
                    }

                    $text = $result;

                    if ($encoding->get('BaseEncoding')->equals('MacRomanEncoding')) {
                        $text = @iconv('Mac', 'UTF-8//TRANSLIT//IGNORE', $text);

                        return $text;
                    }
                }
            }
        }

        // Convert to unicode if not already done.
        if (!$unicode) {

            if ($this->get('Encoding') instanceof Element &&
                $this->get('Encoding')->equals('MacRomanEncoding')
            ) {
                $text = @iconv('Mac', 'UTF-8//TRANSLIT//IGNORE', $text);
            } else {
                $text = @iconv('Windows-1252', 'UTF-8//TRANSLIT//IGNORE', $text);
            }
        }

        return $text;
    }
}

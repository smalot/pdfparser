<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
 *
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
 */

namespace Smalot\PdfParser;

use Smalot\PdfParser\Encoding\WinAnsiEncoding;
use Smalot\PdfParser\Exception\EncodingNotFoundException;

/**
 * Class Font
 */
class Font extends PDFObject
{
    const MISSING = '?';

    /**
     * @var array
     */
    protected $table = null;

    /**
     * @var array
     */
    protected $tableSizes = null;

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
        return $this->has('BaseFont') ? (string) $this->get('BaseFont') : '[Unknown]';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return (string) $this->header->get('Subtype');
    }

    /**
     * @return array
     */
    public function getDetails($deep = true)
    {
        $details = [];

        $details['Name'] = $this->getName();
        $details['Type'] = $this->getType();
        $details['Encoding'] = ($this->has('Encoding') ? (string) $this->get('Encoding') : 'Ansi');

        $details += parent::getDetails($deep);

        return $details;
    }

    /**
     * @param string $char
     * @param bool   $use_default
     *
     * @return string|bool
     */
    public function translateChar($char, $use_default = true)
    {
        $dec = hexdec(bin2hex($char));

        if (\array_key_exists($dec, $this->table)) {
            return $this->table[$dec];
        }

        // fallback for decoding single-byte ANSI characters that are not in the lookup table
        $fallbackDecoded = $char;
        if (
            \strlen($char) < 2
            && $this->has('Encoding')
            && $this->get('Encoding') instanceof Encoding
        ) {
            try {
                if (WinAnsiEncoding::class === $this->get('Encoding')->__toString()) {
                    $fallbackDecoded = self::uchr($dec);
                }
            } catch (EncodingNotFoundException $e) {
                // Encoding->getEncodingClass() throws EncodingNotFoundException when BaseEncoding doesn't exists
                // See table 5.11 on PDF 1.5 specs for more info
            }
        }

        return $use_default ? self::MISSING : $fallbackDecoded;
    }

    /**
     * @param int $code
     *
     * @return string
     */
    public static function uchr($code)
    {
        // html_entity_decode() will not work with UTF-16 or UTF-32 char entities,
        // therefore, we use mb_convert_encoding() instead
        return mb_convert_encoding('&#'.((int) $code).';', 'UTF-8', 'HTML-ENTITIES');
    }

    /**
     * @return array
     */
    public function loadTranslateTable()
    {
        if (null !== $this->table) {
            return $this->table;
        }

        $this->table = [];
        $this->tableSizes = [
            'from' => 1,
            'to' => 1,
        ];

        if ($this->has('ToUnicode')) {
            $content = $this->get('ToUnicode')->getContent();
            $matches = [];

            // Support for multiple spacerange sections
            if (preg_match_all('/begincodespacerange(?P<sections>.*?)endcodespacerange/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
                    $regexp = '/<(?P<from>[0-9A-F]+)> *<(?P<to>[0-9A-F]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    $this->tableSizes = [
                        'from' => max(1, \strlen(current($matches['from'])) / 2),
                        'to' => max(1, \strlen(current($matches['to'])) / 2),
                    ];

                    break;
                }
            }

            // Support for multiple bfchar sections
            if (preg_match_all('/beginbfchar(?P<sections>.*?)endbfchar/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
                    $regexp = '/<(?P<from>[0-9A-F]+)> +<(?P<to>[0-9A-F]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    $this->tableSizes['from'] = max(1, \strlen(current($matches['from'])) / 2);

                    foreach ($matches['from'] as $key => $from) {
                        $parts = preg_split(
                            '/([0-9A-F]{4})/i',
                            $matches['to'][$key],
                            0,
                            \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE
                        );
                        $text = '';
                        foreach ($parts as $part) {
                            $text .= self::uchr(hexdec($part));
                        }
                        $this->table[hexdec($from)] = $text;
                    }
                }
            }

            // Support for multiple bfrange sections
            if (preg_match_all('/beginbfrange(?P<sections>.*?)endbfrange/s', $content, $matches)) {
                foreach ($matches['sections'] as $section) {
                    // Support for : <srcCode1> <srcCode2> <dstString>
                    $regexp = '/<(?P<from>[0-9A-F]+)> *<(?P<to>[0-9A-F]+)> *<(?P<offset>[0-9A-F]+)>[ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    foreach ($matches['from'] as $key => $from) {
                        $char_from = hexdec($from);
                        $char_to = hexdec($matches['to'][$key]);
                        $offset = hexdec($matches['offset'][$key]);

                        for ($char = $char_from; $char <= $char_to; ++$char) {
                            $this->table[$char] = self::uchr($char - $char_from + $offset);
                        }
                    }

                    // Support for : <srcCode1> <srcCodeN> [<dstString1> <dstString2> ... <dstStringN>]
                    // Some PDF file has 2-byte Unicode values on new lines > added \r\n
                    $regexp = '/<(?P<from>[0-9A-F]+)> *<(?P<to>[0-9A-F]+)> *\[(?P<strings>[\r\n<>0-9A-F ]+)\][ \r\n]+/is';

                    preg_match_all($regexp, $section, $matches);

                    foreach ($matches['from'] as $key => $from) {
                        $char_from = hexdec($from);
                        $strings = [];

                        preg_match_all('/<(?P<string>[0-9A-F]+)> */is', $matches['strings'][$key], $strings);

                        foreach ($strings['string'] as $position => $string) {
                            $parts = preg_split(
                                '/([0-9A-F]{4})/i',
                                $string,
                                0,
                                \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE
                            );
                            $text = '';
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
     * @param array $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @param string $hexa
     * @param bool   $add_braces
     *
     * @return string
     */
    public static function decodeHexadecimal($hexa, $add_braces = false)
    {
        // Special shortcut for XML content.
        if (false !== stripos($hexa, '<?xml')) {
            return $hexa;
        }

        $text = '';
        $parts = preg_split('/(<[a-f0-9]+>)/si', $hexa, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (preg_match('/^<.*>$/s', $part) && false === stripos($part, '<?xml')) {
                // strip line breaks
                $part = preg_replace("/[\r\n]/", '', $part);
                $part = trim($part, '<>');
                if ($add_braces) {
                    $text .= '(';
                }

                $part = pack('H*', $part);
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
        $parts = preg_split('/(\\\\[0-7]{3})/s', $text, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);
        $text = '';

        foreach ($parts as $part) {
            if (preg_match('/^\\\\[0-7]{3}$/', $part)) {
                $text .= \chr(octdec(trim($part, '\\')));
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
    public static function decodeEntities($text)
    {
        $parts = preg_split('/(#\d{2})/s', $text, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);
        $text = '';

        foreach ($parts as $part) {
            if (preg_match('/^#\d{2}$/', $part)) {
                $text .= \chr(hexdec(trim($part, '#')));
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
            $text = '';
            $length = \strlen($decode);

            for ($i = 0; $i < $length; $i += 2) {
                $text .= self::uchr(hexdec(bin2hex(substr($decode, $i, 2))));
            }
        }

        return $text;
    }

    /**
     * @return int
     *
     * @todo Deprecated, use $this->config->getFontSpaceLimit() instead.
     */
    protected function getFontSpaceLimit()
    {
        return $this->config->getFontSpaceLimit();
    }

    /**
     * @param array $commands
     *
     * @return string
     */
    public function decodeText($commands)
    {
        $text = '';
        $word_position = 0;
        $words = [];
        $unicode = false;
        $font_space = $this->getFontSpaceLimit();

        foreach ($commands as $command) {
            switch ($command[PDFObject::TYPE]) {
                case 'n':
                    if ((float) (trim($command[PDFObject::COMMAND])) < $font_space) {
                        $word_position = \count($words);
                    }
                    continue 2;
                case '<':
                    // Decode hexadecimal.
                    $text = self::decodeHexadecimal('<'.$command[PDFObject::COMMAND].'>');
                    break;

                default:
                    // Decode octal (if necessary).
                    $text = self::decodeOctal($command[PDFObject::COMMAND]);
            }

            // replace escaped chars
            $text = str_replace(
                ['\\\\', '\(', '\)', '\n', '\r', '\t', '\f', '\ '],
                ['\\', '(', ')', "\n", "\r", "\t", "\f", ' '],
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
            $word = $this->decodeContent($word);
        }

        return implode(' ', $words);
    }

    /**
     * @param string $text
     * @param bool   $unicode This parameter is deprecated and might be removed in a future release
     *
     * @return string
     */
    public function decodeContent($text, &$unicode = null)
    {
        if ($this->has('ToUnicode')) {
            $bytes = $this->tableSizes['from'];

            if ($bytes) {
                $result = '';
                $length = \strlen($text);

                for ($i = 0; $i < $length; $i += $bytes) {
                    $char = substr($text, $i, $bytes);

                    if (false !== ($decoded = $this->translateChar($char, false))) {
                        $char = $decoded;
                    } elseif ($this->has('DescendantFonts')) {
                        if ($this->get('DescendantFonts') instanceof PDFObject) {
                            $fonts = $this->get('DescendantFonts')->getHeader()->getElements();
                        } else {
                            $fonts = $this->get('DescendantFonts')->getContent();
                        }
                        $decoded = false;

                        foreach ($fonts as $font) {
                            if ($font instanceof self) {
                                if (false !== ($decoded = $font->translateChar($char, false))) {
                                    $decoded = mb_convert_encoding($decoded, 'UTF-8', 'Windows-1252');
                                    break;
                                }
                            }
                        }

                        if (false !== $decoded) {
                            $char = $decoded;
                        } else {
                            $char = mb_convert_encoding($char, 'UTF-8', 'Windows-1252');
                        }
                    } else {
                        $char = self::MISSING;
                    }

                    $result .= $char;
                }

                $text = $result;
            }
        } elseif ($this->has('Encoding') && $this->get('Encoding') instanceof Encoding) {
            /** @var Encoding $encoding */
            $encoding = $this->get('Encoding');
            $unicode = mb_check_encoding($text, 'UTF-8');
            $result = '';
            if ($unicode) {
                $chars = preg_split(
                        '//s'.($unicode ? 'u' : ''),
                        $text,
                        -1,
                        \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
                );

                foreach ($chars as $char) {
                    $dec_av = hexdec(bin2hex($char));
                    $dec_ap = $encoding->translateChar($dec_av);
                    $result .= self::uchr($dec_ap);
                }
            } else {
                $length = \strlen($text);

                for ($i = 0; $i < $length; ++$i) {
                    $dec_av = hexdec(bin2hex($text[$i]));
                    $dec_ap = $encoding->translateChar($dec_av);
                    $result .= self::uchr($dec_ap);
                }
            }
            $text = $result;
        } elseif ($this->get('Encoding') instanceof Element &&
                  $this->get('Encoding')->equals('MacRomanEncoding')) {
            // mb_convert_encoding does not support MacRoman/macintosh,
            // so we use iconv() here
            $text = iconv('macintosh', 'UTF-8', $text);
        } elseif (!mb_check_encoding($text, 'UTF-8')) {
            // don't double-encode strings already in UTF-8
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        }

        return $text;
    }
}

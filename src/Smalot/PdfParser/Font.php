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

namespace Smalot\PdfParser;

/**
 * Class Font
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

            if (!($encoding instanceof Encoding) && $encoding instanceof Element) {
                $encoding = $encoding->getContent();
            }

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

        $toUnicode = $this->getHeader()->get('ToUnicode');

        //$toUnicode = ($id?$this->document->resolveXRef($id, true):null);

        return ($this->toUnicode = $toUnicode);
    }

    /**
     * @param string $char
     *
     * @return string
     */
    public function translateChar($char, $is_hexa = false)
    {
        $this->init();

        if ($is_hexa) {
            $char = str_pad($char, 4, '0', STR_PAD_RIGHT);
            $dec  = hexdec($char);
        } else {
            $dec = hexdec(bin2hex($char));
        }

        if (array_key_exists($dec, $this->table['chars'])) {
            return $this->table['chars'][$dec];
        } else {
            foreach ($this->table['ranges'] as $range) {
                if ($dec >= $range['from'] && $dec <= $range['to']) {
                    $new_dec  = ($dec - $range['from']) + $range['offset'];
                    $new_char = html_entity_decode('&#' . $new_dec . ';', ENT_NOQUOTES, 'UTF-8');

                    //$hex     = str_pad(base_convert($new_dec, 10, 16), ($new_dec <= 255 ? 2 : 4), '0', STR_PAD_LEFT);

                    return ($this->table['chars'][$dec] = $new_char);
                }
            }

            return $char;
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
     *
     */
    public function loadTranslateTable()
    {
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
                $bfchar  = substr($content, strpos($content, 'beginbfchar') + strlen('beginbfchar'), strpos($content, 'endbfchar') - strpos($content, 'beginbfchar') - strlen('beginbfchar'));
                $regexp  = '/<(?<from>[0-9A-Z]+)> +<(?<to>[0-9A-Z]+)>[ \r\n]+/ims';
                $matches = array();
                preg_match_all($regexp, $bfchar, $matches);
                foreach ($matches['from'] as $key => $from) {
                    $to = $matches['to'][$key];
                    $to = html_entity_decode('&#' . $to . ';', ENT_NOQUOTES, 'UTF-8');
                    /*if (substr($to, 0, 2) == '00') {
                        $to = substr($to, 2);
                    }*/
                    $this->table['chars'][hexdec($from)] = $to;
                }
            }

            if (strpos($content, 'beginbfrange') !== false) {
                $bfrange = substr($content, strpos($content, 'beginbfrange') + strlen('beginbfrange'), strpos($content, 'endbfrange') - strpos($content, 'beginbfrange') - strlen('beginbfrange'));
                $regexp  = '/<(?<from>[0-9A-Z]+)> *<(?<to>[0-9A-Z]+)> *<(?<offset>[0-9A-Z]+)>[ \r\n]+/ims';
                $matches = array();
                preg_match_all($regexp, $bfrange, $matches);
                foreach ($matches['from'] as $key => $from) {
                    $this->table['ranges'][] = array(
                        'from'   => hexdec($from),
                        'to'     => hexdec($matches['to'][$key]),
                        'offset' => hexdec($matches['offset'][$key]),
                    );
                }
            }
        }
    }
}

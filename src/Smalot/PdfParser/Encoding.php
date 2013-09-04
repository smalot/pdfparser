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

use Smalot\PdfParser\Element\ElementNumeric;

/**
 * Class Encoding
 *
 * @package Smalot\PdfParser
 */
class Encoding extends Object
{
    /**
     * @var array
     */
    protected $encoding;

    /**
     * @var array
     */
    protected $differences;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var bool
     */
    protected $init_done = false;

    /**
     *
     */
    protected function init()
    {
        if ($this->init_done) {
            return;
        } else {
            $this->init_done = true;
        }

        $this->mapping     = array();
        $this->differences = array();
        $this->encoding    = null;

        if ($this->has('BaseEncoding')) {
            // Load reference table charset.
            $baseEncoding = preg_replace('/[^A-Z0-9]/i', '', $this->get('BaseEncoding')->getContent());
            $encoding     = array();
            $file         = __DIR__ . '/Encoding/' . $baseEncoding . '.php';

            if (file_exists($file)) {
                include $file;
            } else {
                die('Missing encoding data file: "' . $file . '".');
            }

            $this->encoding = $encoding;

            // Build table including differences.
            $differences = $this->get('Differences')->getContent();
            $code        = 0;

            foreach ($differences as $difference) {
                /** @var ElementNumeric $difference */
                if ($difference instanceof ElementNumeric) {
                    $code = $difference->getContent();
                    continue;
                }

                // ElementName
                $this->differences[$code] = $difference->getContent();

                // For the next char.
                $code++;
            }

            // Build final mapping (custom => standard).
            $table = array_flip(array_reverse($this->encoding, true));

            foreach ($this->differences as $code => $difference) {
                /** @var string $difference */
                $this->mapping[$code] = $table[$difference];
            }
        }

//        var_dump($this->mapping);
    }

    /**
     * @param string $token
     *
     * @return int
     */
//    public function convertTokenToInt($token)
//    {
//        $this->init();
//
//        var_dump($this->differences);
//        die();
//
//        if (isset($this->mapping[$token])) {
//            return $this->mapping[$token];
//        } else {
//            return 0;
//        }
//    }

    /**
     * @param int $char
     *
     * @return int
     */
    public function translateChar($dec)
    {
        $this->init();

        if (isset($this->mapping[$dec])) {
            $dec = $this->mapping[$dec];
        } elseif (isset($this->encoding[$dec])) {
            $dec = $dec;
        }

        return $dec;
    }
}
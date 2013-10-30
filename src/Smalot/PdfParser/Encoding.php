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
     *
     */
    public function init()
    {
        $this->mapping     = array();
        $this->differences = array();
        $this->encoding    = null;

        if ($this->has('BaseEncoding')) {
            // Load reference table charset.
            $baseEncoding = preg_replace('/[^A-Z0-9]/is', '', $this->get('BaseEncoding')->getContent());
            $className    = '\\Smalot\\PdfParser\\Encoding\\' . $baseEncoding;

            if (class_exists($className)) {
                $class = new $className();
                $this->encoding = $class->getTranslations();
            } else {
                throw new \Exception('Missing encoding data for: "' . $baseEncoding . '".');
            }

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
                $this->mapping[$code] = (isset($table[$difference]) ? $table[$difference] : Font::MISSING);
            }
        }
    }

    /**
     * @return array
     */
    public function getDetails($deep = true)
    {
        $details = array();

        $details['BaseEncoding'] = ($this->has('BaseEncoding') ? (string)$this->get('BaseEncoding') : 'Ansi');
        $details['Differences']  = ($this->has('Differences') ? (string)$this->get('Differences') : '');

        $details += parent::getDetails($deep);

        return $details;
    }

    /**
     * @param int $char
     *
     * @return int
     */
    public function translateChar($dec)
    {
        if (isset($this->mapping[$dec])) {
            $dec = $this->mapping[$dec];
        }

        return $dec;
    }
}

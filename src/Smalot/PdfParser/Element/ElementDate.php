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

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Document;

/**
 * Class ElementDate
 *
 * @package Smalot\PdfParser\Element
 */
class ElementDate extends ElementString
{
    /**
     * @var array
     */
    protected static $formats = array(
        4  => 'Y',
        6  => 'Ym',
        8  => 'Ymd',
        10 => 'YmdH',
        12 => 'YmdHi',
        14 => 'YmdHis',
        15 => 'YmdHise',
        17 => 'YmdHisO',
        19 => 'YmdHisO',
    );

    /**
     * @var string
     */
    protected $format = 'c';

    /**
     * @param \DateTime $value
     * @param Document  $document
     */
    public function __construct($value, Document $document = null)
    {
        if (!($value instanceof \DateTime)) {
            throw new \Exception('DateTime required.');
        }

        parent::__construct($value, null);
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        if ($value instanceof \DateTime) {
            $timestamp = $value->getTimeStamp();
        } else {
            $timestamp = strtotime($value);
        }

        return ($timestamp == $this->value->getTimeStamp());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)($this->value->format($this->format));
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementDate
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*\(D\:(?<name>.*?)\)/s', $content, $match)) {
            $name = $match['name'];
            $name = str_replace("'", '', $name);

            // Smallest format : Y
            // Full format     : YmdHisP
            if (!preg_match('/^\d{4}(\d{2}(\d{2}(\d{2}(\d{2}(\d{2}(Z|[\+-]\d{2}(\d{2})?)?)?)?)?)?)?$/', $name)) {
                return false;
            }

            $format = self::$formats[strlen($name)];
            $date   = \DateTime::createFromFormat($format, $name);
            if (!$date) {
                return false;
            }
            $offset += strpos($content, '(D:') + strlen($match['name']) + 4; // 1 for '(D:' and ')'

            $element = new self($date, $document);
            $element->setFormat($format);

            return $element;
        }

        return false;
    }
}

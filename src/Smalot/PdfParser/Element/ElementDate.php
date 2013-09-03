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
            $name   = $match['name'];
            $offset = strpos($content, '(D:') + strlen($name) + 4; // 1 for '(D:' and ')'

            if (!preg_match('/^[1-2][0-9]{13}[\-+][0-9]{2}\'[0-9]{2}\'$/', $name)) {
                throw new \Exception('Invalid date format.');
            }

            $name = str_replace("'", '', $name);
            $date  = \DateTime::createFromFormat('YmdHisP', $name);

            return new self($date, $document);
        }

        return false;
    }
}

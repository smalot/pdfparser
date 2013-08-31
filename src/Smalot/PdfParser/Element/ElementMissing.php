<?php

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Document;

/**
 * Class ElementMissing
 */
class ElementMissing extends Element
{
    /**
     * @param string   $value
     * @param Document $document
     */
    public function __construct($value, Document $document = null)
    {
        parent::__construct(null, null);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        return false;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($value)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function getContent()
    {
        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}

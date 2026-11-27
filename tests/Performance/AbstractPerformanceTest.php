<?php

namespace PerformanceTests;

use Smalot\PdfParser\Config;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\PDFObject;
use Smalot\PdfParser\XObject\Form;

abstract class AbstractPerformanceTest
{
    /**
     * Initializes the test (eg, fetches the files etc).
     */
    abstract public function init(): void;

    /**
     * Executes the test.
     */
    abstract public function run(): void;

    /**
     * Returns the time over which the test is considered a fail.
     */
    abstract public function getMaxEstimatedTime(): int;

    /**
     * Provides the text array of a page, whose content stream invokes a form
     * XObject with the given content.
     */
    protected function getTextArrayOfFormContent(string $content): array
    {
        // The recursion stack is static and only emptied by Page::getText().
        // An object, whose hash equals an entry of the stack, provides no text.
        PDFObject::$recursionStack = [];

        $document = new Document();
        $document->init();

        $form = new Form($document, null, $content, new Config());
        $header = new Header([
            'Resources' => new Header([
                'XObject' => new Header(['Fr0' => $form]),
            ]),
            'Contents' => new ElementArray([new Element('/Fr0 Do', $document)], $document),
        ]);

        return (new Page($document, $header))->getTextArray();
    }
}

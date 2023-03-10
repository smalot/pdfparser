<?php

declare(strict_types=1);

namespace PHPUnitTests\Unit;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\PDFObject;

class PDFObjectTest extends TestCase
{
    public function testGetTextOnNullPage(): void
    {
        static::assertSame(' ', (new PDFObject(new Document()))->getText());
    }

    public function testGetTextOnPageWithoutContent(): void
    {
        $document = new Document();

        static::assertSame(' ', (new PDFObject($document, null, null))->getText(new Page($document)));
    }
}

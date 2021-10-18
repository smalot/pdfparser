<?php

declare(strict_types=1);

namespace Tests\Smalot\PdfParser\Unit;

use Smalot\PdfParser\Document;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\PDFObject;
use Tests\Smalot\PdfParser\TestCase;

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

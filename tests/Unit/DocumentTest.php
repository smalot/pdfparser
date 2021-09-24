<?php

declare(strict_types=1);

namespace Tests\Smalot\PdfParser\Unit;

use Smalot\PdfParser\Document;
use Tests\Smalot\PdfParser\TestCase;

class DocumentTest extends TestCase
{
    public function testGetFirstFontWhenNoFontAvailable(): void
    {
        $document = new Document();

        $this->assertNull($document->getFirstFont());
    }
}

<?php

declare(strict_types=1);

namespace PHPUnitTests\Unit;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Document;

class DocumentTest extends TestCase
{
    public function testGetFirstFontWhenNoFontAvailable(): void
    {
        $document = new Document();

        $this->assertNull($document->getFirstFont());
    }
}

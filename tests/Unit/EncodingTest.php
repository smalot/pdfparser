<?php

declare(strict_types=1);

namespace Tests\Smalot\PdfParser\Unit;

use Smalot\PdfParser\Document;
use Smalot\PdfParser\Encoding;
use Tests\Smalot\PdfParser\TestCase;

class EncodingTest extends TestCase
{
    public function testTranslateCharOnUnknownGlyph(): void
    {
        $encoding = new Encoding(new Document());

        $this->assertNull($encoding->translateChar('foo'));
    }
}

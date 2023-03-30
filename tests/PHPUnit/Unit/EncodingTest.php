<?php

declare(strict_types=1);

namespace PHPUnitTests\Unit;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Encoding;

class EncodingTest extends TestCase
{
    public function testTranslateCharOnUnknownGlyph(): void
    {
        $encoding = new Encoding(new Document());

        $this->assertNull($encoding->translateChar('foo'));
    }
}

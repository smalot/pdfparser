<?php
declare(strict_types=1);

namespace Tests\Smalot\PdfParser\Unit;

use Smalot\PdfParser\Document;
use Smalot\PdfParser\Encoding;
use Tests\Smalot\PdfParser\TestCase;

/**
 * @coversDefaultClass \Smalot\PdfParser\Encoding
 */
class EncodingTest extends TestCase
{
    /**
     * @covers ::translateChar
     */
    public function testTranslateCharOnUnknownGlyph(): void
    {
        $encoding = new Encoding(new Document());

        static::assertSame('foo', $encoding->translateChar('foo'));
    }
}

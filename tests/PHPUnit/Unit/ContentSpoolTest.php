<?php

declare(strict_types=1);

namespace PHPUnitTests\Unit;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\ContentSpool;

class ContentSpoolTest extends TestCase
{
    public function testStoreAndFetchRoundTrip(): void
    {
        $spool = new ContentSpool();

        $a = 'first chunk of content';
        $b = "second chunk\nwith a newline";

        $refA = $spool->store($a);
        $refB = $spool->store($b);

        $this->assertIsArray($refA);
        $this->assertIsArray($refB);

        // Chunks are appended, so the second starts right after the first
        $this->assertSame([0, \strlen($a)], $refA);
        $this->assertSame([\strlen($a), \strlen($b)], $refB);

        // Fetching does not depend on order and can be repeated
        $this->assertSame($b, $spool->fetch($refB[0], $refB[1]));
        $this->assertSame($a, $spool->fetch($refA[0], $refA[1]));
        $this->assertSame($a, $spool->fetch($refA[0], $refA[1]));
    }

    public function testStoringEmptyContentReturnsNull(): void
    {
        $spool = new ContentSpool();

        $this->assertNull($spool->store(''));
    }

    public function testFetchIsBinarySafe(): void
    {
        $spool = new ContentSpool();

        $binary = random_bytes(2048)."\x00\x1f\x80\xff";
        $ref = $spool->store($binary);

        $this->assertIsArray($ref);
        $this->assertSame($binary, $spool->fetch($ref[0], $ref[1]));
    }
}

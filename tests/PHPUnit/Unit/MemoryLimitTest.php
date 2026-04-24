<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2026-04-24
 *
 * @license LGPLv3
 *
 * @url     <https://github.com/smalot/pdfparser>
 */

namespace PHPUnitTests\Unit;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\RawData\MemoryLimit;

class MemoryLimitTest extends TestCase
{
    public function testToBytesWithGigabytes(): void
    {
        $this->assertSame(1073741824, MemoryLimit::toBytes('1G'));
    }

    public function testToBytesWithMegabytes(): void
    {
        $this->assertSame(268435456, MemoryLimit::toBytes('256M'));
    }

    public function testToBytesWithKilobytes(): void
    {
        $this->assertSame(65536, MemoryLimit::toBytes('64K'));
    }

    public function testToBytesWithoutUnit(): void
    {
        $this->assertSame(2048, MemoryLimit::toBytes('2048'));
    }

    public function testToBytesTrimsInput(): void
    {
        $this->assertSame(33554432, MemoryLimit::toBytes(' 32M '));
    }

    public function testToBytesHandlesLowercaseUnits(): void
    {
        $this->assertSame(1048576, MemoryLimit::toBytes('1m'));
    }

    public function testToBytesReturnsMinusOneForUnlimitedValue(): void
    {
        $this->assertSame(-1, MemoryLimit::toBytes('-1'));
    }

    public function testToBytesReturnsMinusOneForEmptyValue(): void
    {
        $this->assertSame(-1, MemoryLimit::toBytes(''));
    }
}

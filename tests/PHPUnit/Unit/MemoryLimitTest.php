<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Vitor Mattos <1079143+vitormattos@users.noreply.github.com>
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
    /**
     * @dataProvider toBytesProvider
     */
    public function testToBytes(string $input, int $expected): void
    {
        $this->assertSame($expected, MemoryLimit::toBytes($input));
    }

    /**
     * @return array<string,array{0:string,1:int}>
     */
    public static function toBytesProvider(): array
    {
        return [
            'gigabytes' => ['1G', 1073741824],
            'megabytes' => ['256M', 268435456],
            'kilobytes' => ['64K', 65536],
            'without unit' => ['2048', 2048],
            'trimmed value' => [' 32M ', 33554432],
            'lowercase unit' => ['1m', 1048576],
            'unlimited value' => ['-1', -1],
            'empty value' => ['', -1],
        ];
    }
}

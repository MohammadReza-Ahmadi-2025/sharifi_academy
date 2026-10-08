<?php

namespace Tests\Unit;

use App\Support\PhpIniSize;
use PHPUnit\Framework\TestCase;

class PhpIniSizeTest extends TestCase
{
    public function test_converts_megabytes_to_bytes(): void
    {
        $this->assertSame(2 * 1024 * 1024, PhpIniSize::toBytes('2M'));
    }
}

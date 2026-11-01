<?php

namespace Tests\Unit\Support;

use App\Support\SafeCallable;
use RuntimeException;
use Tests\TestCase;

class SafeCallableTest extends TestCase
{
    public function test_returns_callback_result_on_success(): void
    {
        $this->assertSame(42, SafeCallable::run('ok', fn () => 42, 0));
    }

    public function test_returns_fallback_and_reports_on_failure(): void
    {
        $result = SafeCallable::run(
            'boom',
            function (): int {
                throw new RuntimeException('boom');
            },
            7,
        );

        $this->assertSame(7, $result);
    }
}

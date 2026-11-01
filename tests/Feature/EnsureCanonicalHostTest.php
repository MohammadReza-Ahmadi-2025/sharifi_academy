<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureCanonicalHostTest extends TestCase
{
    use RefreshDatabase;

    public function test_canonical_host_middleware_is_skipped_during_tests(): void
    {
        config(['app.url' => 'https://academy-sharifi.ir']);

        $this->get('/')
            ->assertOk();
    }
}

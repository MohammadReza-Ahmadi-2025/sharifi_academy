<?php

namespace Tests\Unit;

use App\Support\SeoSlug;
use PHPUnit\Framework\TestCase;

class SeoSlugTest extends TestCase
{
    public function test_normalize_converts_spaces_to_hyphens(): void
    {
        $this->assertSame(
            'training-in-mechanical-engineering-software',
            SeoSlug::normalize('Training in Mechanical Engineering Software'),
        );
    }

    public function test_for_url_matches_normalize(): void
    {
        $stored = 'Training in Mechanical Engineering Software';

        $this->assertSame(SeoSlug::normalize($stored), SeoSlug::forUrl($stored));
    }
}

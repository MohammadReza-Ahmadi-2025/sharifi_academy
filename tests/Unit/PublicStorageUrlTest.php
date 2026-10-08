<?php

namespace Tests\Unit;

use App\Support\Storage\PublicStorageUrl;
use PHPUnit\Framework\TestCase;

class PublicStorageUrlTest extends TestCase
{
    public function test_for_path_builds_relative_storage_url(): void
    {
        $this->assertSame(
            '/storage/banners/2/file.webp',
            PublicStorageUrl::forPath('banners/2/file.webp'),
        );
    }

    public function test_normalize_strips_wrong_absolute_host(): void
    {
        $this->assertSame(
            '/storage/banners/2/file.webp',
            PublicStorageUrl::normalize('http://localhost/storage/banners/2/file.webp'),
        );
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MediaTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_table_is_polymorphic(): void
    {
        $this->assertTrue(Schema::hasTable('media'));
        $this->assertTrue(Schema::hasColumns('media', [
            'id',
            'model_type',
            'model_id',
            'collection_name',
            'file_name',
            'disk',
            'size',
        ]));
    }
}

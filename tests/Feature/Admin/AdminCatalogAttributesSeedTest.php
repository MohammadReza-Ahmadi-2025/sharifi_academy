<?php

namespace Tests\Feature\Admin;

use App\Modules\Catalog\Models\Attribute;
use App\Modules\Catalog\Models\AttributeValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogAttributesSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_catalog_attributes_are_seeded(): void
    {
        $this->assertGreaterThanOrEqual(5, Attribute::query()->count());
        $this->assertTrue(Attribute::query()->where('slug', 'color')->exists());
        $this->assertTrue(Attribute::query()->where('slug', 'size')->exists());

        $color = Attribute::query()->where('slug', 'color')->firstOrFail();
        $this->assertGreaterThanOrEqual(3, AttributeValue::query()->where('attribute_id', $color->id)->count());
    }
}

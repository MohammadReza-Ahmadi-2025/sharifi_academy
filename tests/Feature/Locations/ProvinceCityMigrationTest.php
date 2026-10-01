<?php

namespace Tests\Feature\Locations;

use App\Modules\Locations\Models\ProvinceCity;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvinceCityMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_province_city_hierarchy_can_be_persisted(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::where('mobile', '09120000001')->firstOrFail();

        $province = ProvinceCity::query()->create([
            'title' => 'تهران',
            'vista_id' => 100,
            'user_id' => $owner->id,
            'sort' => 1,
        ]);

        $city = ProvinceCity::query()->create([
            'title' => 'تهران',
            'parent_id' => $province->id,
            'vista_id' => 1001,
            'user_id' => $owner->id,
            'sort' => 1,
        ]);

        $this->assertTrue($province->isProvince());
        $this->assertTrue($city->isCity());
        $this->assertSame($province->id, $city->parent_id);
        $this->assertCount(1, $province->fresh()->children);
    }
}

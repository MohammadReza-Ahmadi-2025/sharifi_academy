<?php

namespace Database\Seeders;

use App\Modules\Locations\Models\ProvinceCity;
use Illuminate\Database\Seeder;

class ProvinceCitySeeder extends Seeder
{
    public function run(): void
    {
        $tehran = ProvinceCity::query()->firstOrCreate(
            ['title' => 'تهران', 'parent_id' => null],
        );

        ProvinceCity::query()->firstOrCreate(
            ['title' => 'تهران', 'parent_id' => $tehran->id],
        );

        $isfahan = ProvinceCity::query()->firstOrCreate(
            ['title' => 'اصفهان', 'parent_id' => null],
        );

        ProvinceCity::query()->firstOrCreate(
            ['title' => 'اصفهان', 'parent_id' => $isfahan->id],
        );
    }
}

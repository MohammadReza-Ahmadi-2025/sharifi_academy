<?php

namespace App\Modules\Profile\Support;

use App\Modules\Locations\Models\ProvinceCity;

class LocationSelectOptions
{
    /**
     * @return array{provinces: list<array{id: int, title: string}>, cities: list<array{id: int, title: string, provinceId: int}>}
     */
    public static function forForms(): array
    {
        $provinces = ProvinceCity::query()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->orderBy('title')
            ->get(['id', 'title']);

        $cities = ProvinceCity::query()
            ->whereNotNull('parent_id')
            ->orderBy('sort')
            ->orderBy('title')
            ->get(['id', 'title', 'parent_id']);

        return [
            'provinces' => $provinces->map(fn (ProvinceCity $row) => [
                'id' => $row->id,
                'title' => $row->title,
            ])->values()->all(),
            'cities' => $cities->map(fn (ProvinceCity $row) => [
                'id' => $row->id,
                'title' => $row->title,
                'provinceId' => (int) $row->parent_id,
            ])->values()->all(),
        ];
    }
}

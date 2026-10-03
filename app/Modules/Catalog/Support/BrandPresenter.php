<?php

namespace App\Modules\Catalog\Support;

use App\Modules\Catalog\Models\Brand;

class BrandPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Brand $brand): array
    {
        return [
            'id' => $brand->id,
            'title' => $brand->title,
            'slug' => $brand->slug,
            'status' => (bool) $brand->status,
            'sort' => $brand->sort,
            'website' => $brand->website,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Brand $brand): array
    {
        return [
            'id' => $brand->id,
            'title' => $brand->title,
            'slug' => $brand->slug,
            'description' => $brand->description ?? '',
            'website' => $brand->website ?? '',
            'status' => (bool) $brand->status,
            'sort' => $brand->sort,
            'meta_title' => $brand->meta_title ?? '',
            'meta_description' => $brand->meta_description ?? '',
            'meta_keywords' => is_array($brand->meta_keywords)
                ? implode('، ', $brand->meta_keywords)
                : '',
            'canonical' => $brand->canonical ?? '',
        ];
    }
}

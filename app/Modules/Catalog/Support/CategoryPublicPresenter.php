<?php

namespace App\Modules\Catalog\Support;

use App\Modules\Catalog\Models\Category;

class CategoryPublicPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forPage(Category $category): array
    {
        $imageUrl = $category->relationLoaded('image') && $category->image
            ? $category->image->publicUrl()
            : null;

        return [
            'id' => $category->id,
            'title' => $category->title,
            'slug' => $category->slug,
            'description' => $category->description,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'seo' => CategorySeo::forCategoryShow($category),
        ];
    }
}

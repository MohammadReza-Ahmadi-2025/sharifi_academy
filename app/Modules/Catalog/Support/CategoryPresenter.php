<?php

namespace App\Modules\Catalog\Support;

use App\Modules\Catalog\Models\Category;
use App\Modules\Files\Support\FilePresenter;

class CategoryPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Category $category): array
    {
        $category->loadMissing('parent');

        return [
            'id' => $category->id,
            'title' => $category->title,
            'slug' => $category->slug,
            'parent_title' => $category->parent?->title,
            'sort' => $category->sort,
            'status' => (int) $category->status,
            'show_in_menu' => (bool) $category->show_in_menu,
            'type' => $category->type,
            'type_label' => AdminCategoryTypes::label($category->type),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Category $category): array
    {
        $category->loadMissing('image');

        return [
            'id' => $category->id,
            'parent_id' => $category->parent_id,
            'title' => $category->title,
            'slug' => $category->slug,
            'description' => $category->description,
            'sort' => $category->sort,
            'status' => (int) $category->status === 1,
            'show_in_menu' => (bool) $category->show_in_menu,
            'meta_title' => $category->meta_title ?? '',
            'meta_description' => $category->meta_description ?? '',
            'meta_keywords' => ProductPresenter::metaKeywordsForInput($category->meta_keywords),
            'canonical' => $category->canonical ?? '',
            'image' => $category->image ? FilePresenter::forUploader($category->image) : null,
        ];
    }
}

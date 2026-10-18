<?php

namespace App\Modules\Pages\Support;

use App\Modules\Pages\Models\Page;

class PagePresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Page $page): array
    {
        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'is_active' => $page->is_active,
            'updated_at' => $page->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Page $page): array
    {
        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content ?? '',
            'is_active' => $page->is_active,
            'meta_title' => $page->meta_title ?? '',
            'meta_description' => $page->meta_description ?? '',
            'meta_keywords' => is_array($page->meta_keywords)
                ? implode(', ', $page->meta_keywords)
                : (string) ($page->meta_keywords ?? ''),
            'canonical' => $page->canonical ?? '',
        ];
    }
}

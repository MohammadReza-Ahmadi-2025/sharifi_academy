<?php

namespace App\Modules\Pages\Support;

use App\Modules\Pages\Models\Page;

class AdminPageOptions
{
    /**
     * @return list<array{value: int, label: string}>
     */
    public static function forSelect(): array
    {
        return Page::query()
            ->active()
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Page $page) => [
                'value' => $page->id,
                'label' => $page->title,
            ])
            ->values()
            ->all();
    }
}

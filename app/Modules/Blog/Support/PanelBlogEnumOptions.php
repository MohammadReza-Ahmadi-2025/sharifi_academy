<?php

namespace App\Modules\Blog\Support;

use App\Modules\Blog\Enums\PostStatus;

class PanelBlogEnumOptions
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public static function postStatusOptions(): array
    {
        return [
            ['value' => PostStatus::Draft->value, 'label' => PostStatus::Draft->label()],
            ['value' => PostStatus::PendingReview->value, 'label' => PostStatus::PendingReview->label()],
        ];
    }
}

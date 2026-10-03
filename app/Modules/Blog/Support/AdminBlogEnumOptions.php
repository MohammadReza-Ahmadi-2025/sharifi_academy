<?php

namespace App\Modules\Blog\Support;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Courses\Support\AdminEnumOptions;

class AdminBlogEnumOptions
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public static function postStatusOptions(): array
    {
        return AdminEnumOptions::fromEnum(PostStatus::class);
    }
}

<?php

namespace App\Modules\Comments\Support;

use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Courses\Support\AdminEnumOptions;

class AdminCommentEnumOptions
{
    /**
     * @return list<array{value: int, label: string}>
     */
    public static function statusOptions(): array
    {
        return AdminEnumOptions::fromEnum(CommentStatus::class);
    }
}

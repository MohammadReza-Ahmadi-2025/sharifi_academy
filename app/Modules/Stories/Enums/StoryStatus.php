<?php

namespace App\Modules\Stories\Enums;

enum StoryStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
    case Removed = 'removed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::Published => 'منتشرشده',
            self::Archived => 'بایگانی‌شده',
            self::Removed => 'حذف‌شده',
        };
    }
}

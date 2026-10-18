<?php

namespace App\Modules\Courses\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::PendingReview => 'در انتظار تایید ادمین',
            self::Published => 'منتشرشده',
            self::Archived => 'بایگانی',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

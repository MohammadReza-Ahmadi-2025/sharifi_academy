<?php

namespace App\Modules\Blog\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::PendingReview => 'در انتظار تایید',
            self::Published => 'منتشر شده',
        };
    }

    /**
     * Statuses panel authors (student/instructor) may set themselves.
     *
     * @return list<string>
     */
    public static function panelWritableValues(): array
    {
        return [
            self::Draft->value,
            self::PendingReview->value,
        ];
    }
}

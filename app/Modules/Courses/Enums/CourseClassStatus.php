<?php

namespace App\Modules\Courses\Enums;

enum CourseClassStatus: string
{
    case Draft = 'draft';
    case Upcoming = 'upcoming';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::Upcoming => 'برنامه‌ریزی‌شده',
            self::Ongoing => 'در حال برگزاری',
            self::Completed => 'پایان‌یافته',
            self::Cancelled => 'لغوشده',
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

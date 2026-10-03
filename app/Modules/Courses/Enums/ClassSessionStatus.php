<?php

namespace App\Modules\Courses\Enums;

enum ClassSessionStatus: string
{
    case Scheduled = 'scheduled';
    case Live = 'live';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Postponed = 'postponed';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'برنامه‌ریزی‌شده و هنوز برگزار نشده',
            self::Live => 'در حال برگزاری',
            self::Completed => 'برگزارشده و پایان‌یافته',
            self::Cancelled => 'لغوشده',
            self::Postponed => 'به زمان دیگری منتقل‌شده',
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

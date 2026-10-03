<?php

namespace App\Modules\Courses\Enums;

enum SessionAttendanceSource: string
{
    case System = 'system';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::System => 'سیستم / ادوبی',
            self::Manual => 'ثبت دستی',
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

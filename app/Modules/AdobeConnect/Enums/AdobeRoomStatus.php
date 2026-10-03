<?php

namespace App\Modules\AdobeConnect\Enums;

enum AdobeRoomStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'فعال',
            self::Archived => 'بایگانی',
            self::Error => 'خطا در همگام‌سازی',
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

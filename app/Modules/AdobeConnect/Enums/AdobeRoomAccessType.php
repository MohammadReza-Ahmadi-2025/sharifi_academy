<?php

namespace App\Modules\AdobeConnect\Enums;

enum AdobeRoomAccessType: string
{
    case Protected = 'protected';
    case Public = 'public';
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Protected => 'محافظت‌شده (فقط اعضا و ثبت‌نامی‌ها)',
            self::Public => 'عمومی (با لینک)',
            self::Private => 'خصوصی',
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

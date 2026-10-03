<?php

namespace App\Modules\Stories\Enums;

enum StoryVisibility: string
{
    case Public = 'public';
    case Authenticated = 'authenticated';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'عمومی',
            self::Authenticated => 'فقط کاربران واردشده',
        };
    }
}

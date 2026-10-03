<?php

namespace App\Modules\Home\Enums;

enum BannerLocation: string
{
    case Section = 'section';
    case BlogHero = 'blog_hero';

    public function label(): string
    {
        return match ($this) {
            self::Section => 'سکشن صفحه اصلی',
            self::BlogHero => 'هدر وبلاگ',
        };
    }
}

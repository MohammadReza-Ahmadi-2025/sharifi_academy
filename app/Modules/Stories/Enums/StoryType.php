<?php

namespace App\Modules\Stories\Enums;

enum StoryType: string
{
    case Text = 'text';
    case Image = 'image';
    case Video = 'video';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'متنی',
            self::Image => 'تصویری',
            self::Video => 'ویدیویی',
        };
    }
}

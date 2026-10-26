<?php

namespace App\Modules\Courses\Enums;

enum CourseClassOnlinePlatform: string
{
    case AdobeConnect = 'adobe_connect';
    case VideoLink = 'video_link';

    public function label(): string
    {
        return match ($this) {
            self::AdobeConnect => 'Adobe Connect',
            self::VideoLink => 'لینک ویدیو (آنلاین)',
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

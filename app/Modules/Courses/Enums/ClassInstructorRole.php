<?php

namespace App\Modules\Courses\Enums;

enum ClassInstructorRole: string
{
    case Lead = 'lead';
    case Assistant = 'assistant';
    case Mentor = 'mentor';

    public function label(): string
    {
        return match ($this) {
            self::Lead => 'استاد اصلی',
            self::Assistant => 'استادیار / حل تمرین',
            self::Mentor => 'منتور',
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

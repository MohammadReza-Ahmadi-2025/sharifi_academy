<?php

namespace App\Modules\Courses\Enums;

enum SessionAttendanceRole: string
{
    case Student = 'student';
    case Instructor = 'instructor';
    case Assistant = 'assistant';
    case Guest = 'guest';

    public function label(): string
    {
        return match ($this) {
            self::Student => 'دانشجو',
            self::Instructor => 'استاد',
            self::Assistant => 'دستیار',
            self::Guest => 'مهمان',
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

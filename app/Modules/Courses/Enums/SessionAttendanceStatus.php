<?php

namespace App\Modules\Courses\Enums;

enum SessionAttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Excused = 'excused';
    case Late = 'late';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'حاضر',
            self::Absent => 'غایب',
            self::Excused => 'موجه / مرخصی',
            self::Late => 'با تأخیر',
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

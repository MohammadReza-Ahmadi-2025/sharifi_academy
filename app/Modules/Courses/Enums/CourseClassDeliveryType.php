<?php

namespace App\Modules\Courses\Enums;

enum CourseClassDeliveryType: string
{
    case Online = 'online';
    case InPerson = 'in_person';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'کاملاً آنلاین',
            self::InPerson => 'کاملاً حضوری',
            self::Hybrid => 'ترکیبی',
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

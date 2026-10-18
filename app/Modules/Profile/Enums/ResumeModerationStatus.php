<?php

namespace App\Modules\Profile\Enums;

enum ResumeModerationStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::Pending => 'در انتظار تایید ادمین',
            self::Approved => 'تایید شده (عمومی)',
            self::Rejected => 'رد شده',
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

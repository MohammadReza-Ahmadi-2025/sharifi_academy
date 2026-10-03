<?php

namespace App\Modules\Comments\Enums;

enum CommentStatus: int
{
    case Pending = 0;
    case Approved = 1;
    case Rejected = 2;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار',
            self::Approved => 'تأیید شده',
            self::Rejected => 'رد شده',
        };
    }
}

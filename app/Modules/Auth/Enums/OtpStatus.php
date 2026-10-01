<?php

namespace App\Modules\Auth\Enums;

enum OtpStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Expired = 'expired';
    case Blocked = 'blocked';
    case Failed = 'failed';
}

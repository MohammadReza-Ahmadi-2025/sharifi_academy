<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\OtpSender;
use Illuminate\Support\Facades\Log;

class LogOtpSender implements OtpSender
{
    public function send(string $mobile, string $code, string $type): void
    {
        Log::info('OTP sent', [
            'mobile' => $mobile,
            'type' => $type,
            'code' => $code,
        ]);
    }
}

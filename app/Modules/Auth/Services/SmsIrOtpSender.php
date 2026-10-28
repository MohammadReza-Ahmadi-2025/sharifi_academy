<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\OtpSender;

class SmsIrOtpSender implements OtpSender
{
    public function __construct(
        private readonly SmsIrService $smsIrService,
    ) {}

    public function send(string $mobile, string $code, string $type): void
    {
        $templateKey = 'login';

        $this->smsIrService->sendVerify($templateKey, $mobile, [
            'OTP' => $code,
        ]);
    }
}

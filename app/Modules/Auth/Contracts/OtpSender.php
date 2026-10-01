<?php

namespace App\Modules\Auth\Contracts;

interface OtpSender
{
    public function send(string $mobile, string $code, string $type): void;
}

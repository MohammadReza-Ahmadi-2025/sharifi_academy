<?php

namespace App\Modules\Auth\Contracts;

interface OtpCodeGenerator
{
    public function generate(int $length): string;
}

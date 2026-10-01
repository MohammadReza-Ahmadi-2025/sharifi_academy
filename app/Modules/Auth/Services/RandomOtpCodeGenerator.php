<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\OtpCodeGenerator;

class RandomOtpCodeGenerator implements OtpCodeGenerator
{
    public function generate(int $length): string
    {
        $max = (10 ** $length) - 1;
        $min = 10 ** ($length - 1);

        return str_pad((string) random_int($min, $max), $length, '0', STR_PAD_LEFT);
    }
}

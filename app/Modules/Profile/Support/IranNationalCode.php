<?php

namespace App\Modules\Profile\Support;

final class IranNationalCode
{
    public static function isValid(?string $code): bool
    {
        if ($code === null || $code === '') {
            return true;
        }

        if (! preg_match('/^\d{10}$/', $code)) {
            return false;
        }

        if (preg_match('/^(\d)\1{9}$/', $code)) {
            return false;
        }

        $check = (int) $code[9];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $code[$i] * (10 - $i);
        }

        $remainder = $sum % 11;

        return ($remainder < 2 && $check === $remainder)
            || ($remainder >= 2 && $check === 11 - $remainder);
    }
}

<?php

namespace App\Modules\Auth\Support;

final class MobileNormalizer
{
    public static function toWesternDigits(string $value): string
    {
        return str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $value,
        );
    }

    public static function normalize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return preg_replace('/\D/', '', self::toWesternDigits(trim($value))) ?? '';
    }

    public static function isValidIranMobile(string $mobile): bool
    {
        return (bool) preg_match('/^09\d{9}$/', $mobile);
    }
}

<?php

namespace App\Support;

class PhpIniSize
{
    public static function toBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        if (in_array($unit, ['g', 'm', 'k'], true)) {
            $number = (float) substr($value, 0, -1);
        } else {
            $unit = 'b';
        }

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    public static function format(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1).'MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).'KB';
        }

        return $bytes.'B';
    }
}

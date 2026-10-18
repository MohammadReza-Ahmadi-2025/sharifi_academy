<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Morilog\Jalali\Jalalian;

class PersianDate
{
    public static function formatDate(?CarbonInterface $date, string $format = 'j F Y'): ?string
    {
        if ($date === null) {
            return null;
        }

        return Jalalian::fromDateTime($date)->format($format);
    }

    public static function formatDateTime(?CarbonInterface $date, bool $includeTime = true): ?string
    {
        if ($date === null) {
            return null;
        }

        $jalali = Jalalian::fromDateTime($date);
        $label = $jalali->format('j F Y');

        if (! $includeTime) {
            return $label;
        }

        return $label.'، '.$jalali->format('H:i');
    }

    public static function formatDisplayValue(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'بله' : 'خیر';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        $string = trim((string) $value);

        if ($string === '') {
            return '—';
        }

        if ($string === 'نامحدود') {
            return $string;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $string) !== 1) {
            return $string;
        }

        try {
            $hasTime = preg_match('/\d{1,2}:\d{2}/', $string) === 1;
            $formatted = self::formatDateTime(Carbon::parse($string), $hasTime);

            return $formatted ?? $string;
        } catch (\Throwable) {
            return $string;
        }
    }
}

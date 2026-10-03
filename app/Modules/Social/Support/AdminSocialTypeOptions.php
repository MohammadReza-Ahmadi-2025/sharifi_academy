<?php

namespace App\Modules\Social\Support;

class AdminSocialTypeOptions
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public static function all(): array
    {
        return collect(self::map())
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function map(): array
    {
        return [
            'instagram' => 'اینستاگرام',
            'telegram' => 'تلگرام',
            'whatsapp' => 'واتساپ',
            'twitter' => 'توییتر / ایکس',
            'linkedin' => 'لینکدین',
            'youtube' => 'یوتیوب',
            'aparat' => 'آپارات',
            'phone' => 'تلفن',
            'email' => 'ایمیل',
            'website' => 'وب‌سایت',
            'sms' => 'پیامک / sms',
        ];
    }
}

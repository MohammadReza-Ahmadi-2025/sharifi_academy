<?php

namespace App\Support\Activity;

class ActivityLogFieldLabels
{
    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'title' => 'عنوان',
            'name' => 'نام',
            'slug' => 'نامک',
            'description' => 'توضیحات',
            'status' => 'وضعیت',
            'sort' => 'ترتیب',
            'website' => 'وب‌سایت',
            'meta_title' => 'عنوان متا',
            'meta_description' => 'توضیح متا',
            'meta_keywords' => 'کلمات کلیدی',
            'canonical' => 'آدرس کاننیکال',
            'email' => 'ایمیل',
            'mobile' => 'موبایل',
            'password' => 'رمز عبور',
            'is_active' => 'فعال',
            'type' => 'نوع',
            'value' => 'مقدار',
            'label' => 'برچسب',
            'key' => 'کلید',
            'group' => 'گروه',
            'content' => 'محتوا',
            'body' => 'متن',
            'price' => 'قیمت',
            'sku' => 'کد کالا',
            'brand_id' => 'برند',
            'parent_id' => 'دسته والد',
            'views' => 'بازدید',
            'target_blank' => 'باز شدن در تب جدید',
        ];
    }

    public static function label(string $field): string
    {
        return self::all()[$field] ?? $field;
    }

    public static function formatValue(mixed $value): string
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

        return (string) $value;
    }
}

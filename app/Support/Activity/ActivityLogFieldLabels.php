<?php

namespace App\Support\Activity;

use App\Support\PersianDate;

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
            'settings' => 'تنظیمات',
            'location' => 'محل نمایش',
            'url' => 'آدرس',
            'icon' => 'آیکن',
            'content_type' => 'نوع محتوا',
            'source_type' => 'روش نمایش',
            'limit' => 'حداکثر آیتم',
            'sort_order' => 'ترتیب تب',
            'is_active' => 'فعال',
            'button_text' => 'متن دکمه',
            'button_link' => 'لینک دکمه',
            'subtitle' => 'زیرعنوان',
            'starts_at' => 'شروع نمایش',
            'ends_at' => 'پایان نمایش',
            'display_order' => 'ترتیب نمایش',
            'show_in_menu' => 'نمایش در منو',
            'is_active' => 'فعال',
            'canonical' => 'آدرس کاننیکال',
        ];
    }

    public static function label(string $field): string
    {
        return self::all()[$field] ?? $field;
    }

    public static function formatValue(mixed $value): string
    {
        return PersianDate::formatDisplayValue($value);
    }
}

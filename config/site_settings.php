<?php

return [

    'groups' => [
        'general' => 'عمومی',
        'contact' => 'تماس و پشتیبانی',
        'seo' => 'سئو و محتوا',
        'integrations' => 'کلیدها و یکپارچه‌سازی',
    ],

    /*
    |--------------------------------------------------------------------------
    | تعاریف تنظیمات (کلیدها ثابت؛ مقدار در دیتابیس)
    |--------------------------------------------------------------------------
    |
    | type: text | textarea | number | color | switch | image
    |
    */
    'definitions' => [
        'site_name' => [
            'type' => 'text',
            'group' => 'general',
            'label' => 'نام سایت',
            'autoload' => true,
            'default' => null,
        ],
        'site_description' => [
            'type' => 'textarea',
            'group' => 'general',
            'label' => 'توضیح کوتاه سایت',
            'autoload' => true,
            'default' => null,
        ],
        'site_logo' => [
            'type' => 'image',
            'group' => 'general',
            'label' => 'لوگو',
            'autoload' => true,
            'default' => null,
        ],
        'site_icon' => [
            'type' => 'image',
            'group' => 'general',
            'label' => 'فاویکون',
            'autoload' => true,
            'default' => null,
        ],
        'site_color' => [
            'type' => 'color',
            'group' => 'general',
            'label' => 'رنگ اصلی برند',
            'autoload' => true,
            'default' => '#00489e',
        ],
        'maintenance_mode' => [
            'type' => 'switch',
            'group' => 'general',
            'label' => 'حالت تعمیر (غیرفعال کردن سایت عمومی)',
            'autoload' => true,
            'default' => '0',
        ],
        'home_hero_badge' => [
            'type' => 'text',
            'group' => 'general',
            'label' => 'صفحه خانه — برچسب هیرو',
            'autoload' => true,
            'default' => 'آکادمی تخصصی مهندسی',
        ],
        'home_hero_title' => [
            'type' => 'text',
            'group' => 'general',
            'label' => 'صفحه خانه — عنوان هیرو',
            'autoload' => true,
            'default' => 'مهارت مهندسی را عملی یاد بگیرید',
        ],
        'home_hero_subtitle' => [
            'type' => 'textarea',
            'group' => 'general',
            'label' => 'صفحه خانه — متن هیرو',
            'autoload' => true,
            'default' => null,
        ],
        'home_hero_cta_primary' => [
            'type' => 'text',
            'group' => 'general',
            'label' => 'صفحه خانه — دکمه اصلی هیرو',
            'autoload' => true,
            'default' => 'مشاهده دوره‌ها',
        ],
        'home_hero_cta_secondary' => [
            'type' => 'text',
            'group' => 'general',
            'label' => 'صفحه خانه — دکمه ثانویه هیرو',
            'autoload' => true,
            'default' => 'ورود / ثبت‌نام',
        ],

        'admin_email' => [
            'type' => 'text',
            'group' => 'contact',
            'label' => 'ایمیل مدیر',
            'autoload' => false,
            'default' => null,
        ],
        'contact_email' => [
            'type' => 'text',
            'group' => 'contact',
            'label' => 'ایمیل تماس',
            'autoload' => true,
            'default' => null,
        ],
        'contact_phone' => [
            'type' => 'text',
            'group' => 'contact',
            'label' => 'تلفن تماس',
            'autoload' => true,
            'default' => null,
        ],
        'address' => [
            'type' => 'textarea',
            'group' => 'contact',
            'label' => 'آدرس',
            'autoload' => true,
            'default' => null,
        ],

        'seo_meta_title' => [
            'type' => 'text',
            'group' => 'seo',
            'label' => 'عنوان متا (سراسری)',
            'autoload' => true,
            'default' => null,
        ],
        'seo_meta_description' => [
            'type' => 'textarea',
            'group' => 'seo',
            'label' => 'توضیحات متا (سراسری)',
            'autoload' => true,
            'default' => null,
        ],
        'seo_meta_keywords' => [
            'type' => 'text',
            'group' => 'seo',
            'label' => 'کلمات کلیدی (با ویرگول جدا کنید)',
            'autoload' => false,
            'default' => null,
        ],
        'seo_og_image' => [
            'type' => 'image',
            'group' => 'seo',
            'label' => 'تصویر اشتراک‌گذاری (OG)',
            'autoload' => true,
            'default' => null,
        ],
        'blogs_meta_title' => [
            'type' => 'text',
            'group' => 'seo',
            'label' => 'عنوان متا — لیست وبلاگ',
            'autoload' => false,
            'default' => null,
        ],
        'blogs_meta_description' => [
            'type' => 'textarea',
            'group' => 'seo',
            'label' => 'توضیحات متا — لیست وبلاگ',
            'autoload' => false,
            'default' => null,
        ],
        'blogs_meta_keywords' => [
            'type' => 'text',
            'group' => 'seo',
            'label' => 'کلمات کلیدی — وبلاگ',
            'autoload' => false,
            'default' => null,
        ],
        'blogs_header_title' => [
            'type' => 'text',
            'group' => 'seo',
            'label' => 'عنوان هدر صفحه وبلاگ',
            'autoload' => false,
            'default' => null,
        ],
        'blogs_subheader_title' => [
            'type' => 'textarea',
            'group' => 'seo',
            'label' => 'زیرعنوان صفحه وبلاگ',
            'autoload' => false,
            'default' => null,
        ],
        'google_analytics_id' => [
            'type' => 'text',
            'group' => 'seo',
            'label' => 'شناسه Google Analytics',
            'autoload' => false,
            'default' => null,
        ],
        'google_search_console' => [
            'type' => 'text',
            'group' => 'seo',
            'label' => 'کد تأیید Google Search Console',
            'autoload' => false,
            'default' => null,
        ],

        'recaptcha_site_key' => [
            'type' => 'text',
            'group' => 'integrations',
            'label' => 'reCAPTCHA — کلید سایت',
            'autoload' => false,
            'default' => null,
        ],
        'recaptcha_secret_key' => [
            'type' => 'text',
            'group' => 'integrations',
            'label' => 'reCAPTCHA — کلید مخفی',
            'autoload' => false,
            'default' => null,
        ],
        'sms_api_key' => [
            'type' => 'text',
            'group' => 'integrations',
            'label' => 'کلید API پیامک (در صورت استفاده)',
            'autoload' => false,
            'default' => null,
        ],
    ],

];

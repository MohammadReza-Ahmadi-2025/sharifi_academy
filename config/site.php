<?php

return [

    /*
    |--------------------------------------------------------------------------
    | منوی اصلی سایت
    |--------------------------------------------------------------------------
    |
    | type: link | mega
    | mega_source: course_categories | product_categories (از دیتابیس)
    | visibility: all | guest | auth
    |
    */
    'navigation' => [
        'items' => [
            [
                'id' => 'home',
                'label' => 'خانه',
                'url' => '/',
                'type' => 'link',
                'visibility' => 'all',
            ],
            [
                'id' => 'courses',
                'label' => 'دوره‌ها',
                'url' => '/#courses',
                'type' => 'mega',
                'visibility' => 'all',
                'mega_source' => 'course_categories',
            ],
            [
                'id' => 'books',
                'label' => 'کتاب‌ها',
                'url' => '/#books',
                'type' => 'mega',
                'visibility' => 'all',
                'mega_source' => 'product_categories',
            ],
            [
                'id' => 'account',
                'label' => 'حساب من',
                'url' => '/student/dashboard',
                'type' => 'mega',
                'visibility' => 'auth',
                'mega' => [
                    'columns' => [
                        [
                            'title' => 'پنل‌ها',
                            'links' => [
                                [
                                    'label' => 'پنل دانشجو',
                                    'url' => '/student/dashboard',
                                    'description' => 'دوره‌ها، جلسات و دانلود کتاب',
                                ],
                                [
                                    'label' => 'پنل استاد',
                                    'url' => '/instructor',
                                    'description' => 'مدیریت دوره و جلسات',
                                    'roles' => ['instructor', 'admin', 'super-admin'],
                                ],
                                [
                                    'label' => 'پنل ادمین',
                                    'url' => '/admin',
                                    'description' => 'مدیریت محتوا',
                                    'roles' => ['admin', 'super-admin'],
                                ],
                            ],
                        ],
                        [
                            'title' => 'حساب',
                            'links' => [
                                [
                                    'label' => 'پروفایل من',
                                    'url' => '/profile',
                                    'description' => 'اطلاعات شخصی، آواتار و رمز عبور',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'auth',
                'label' => 'ورود / ثبت‌نام',
                'url' => '/login',
                'type' => 'link',
                'visibility' => 'guest',
            ],
        ],
        'cta' => null,
    ],

];

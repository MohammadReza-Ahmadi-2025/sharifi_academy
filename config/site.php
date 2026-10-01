<?php

return [

    /*
    |--------------------------------------------------------------------------
    | منوی اصلی سایت (موقت — بعداً از پنل ادمین / دیتابیس)
    |--------------------------------------------------------------------------
    |
    | type: link | mega
    | visibility: all | guest | auth
    | roles: فقط وقتی کاربر لاگین است (اختیاری)
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
                'mega' => [
                    'view_all' => [
                        'label' => 'مشاهده همه دوره‌ها',
                        'url' => '/#courses',
                    ],
                    'featured' => [
                        'title' => 'کلاس آنلاین زنده',
                        'description' => 'با اساتید آکادمی در Adobe Connect شرکت کنید و مهارت‌های مهندسی را عملی یاد بگیرید.',
                        'url' => '/#courses',
                        'image' => '/coursetest.png',
                    ],
                    'columns' => [
                        [
                            'title' => 'رشته‌ها',
                            'links' => [
                                [
                                    'label' => 'مهندسی مکانیک',
                                    'url' => '/#courses',
                                    'description' => 'طراحی، شبیه‌سازی و تولید',
                                ],
                                [
                                    'label' => 'مهندسی صنایع',
                                    'url' => '/#courses',
                                    'description' => 'بهینه‌سازی و مدیریت تولید',
                                ],
                                [
                                    'label' => 'نرم‌افزارهای CAD',
                                    'url' => '/#courses',
                                    'description' => 'CATIA، SolidWorks و …',
                                ],
                            ],
                        ],
                        [
                            'title' => 'نوع دوره',
                            'links' => [
                                [
                                    'label' => 'کلاس ترمیک',
                                    'url' => '/#courses',
                                    'description' => 'جلسات زمان‌بندی‌شده با استاد',
                                ],
                                [
                                    'label' => 'کارگاه فشرده',
                                    'url' => '/#courses',
                                    'description' => 'پروژه‌محور در چند جلسه',
                                ],
                                [
                                    'label' => 'ثبت‌نام گروهی',
                                    'url' => '/login',
                                    'description' => 'برای تیم‌ها و شرکت‌ها',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'books',
                'label' => 'کتاب‌ها',
                'url' => '/#books',
                'type' => 'mega',
                'visibility' => 'all',
                'mega' => [
                    'view_all' => [
                        'label' => 'مشاهده همه کتاب‌ها',
                        'url' => '/#books',
                    ],
                    'featured' => [
                        'title' => 'کتاب‌های تخصصی آکادمی',
                        'description' => 'منابع PDF برای مرور، تمرین و همراهی با دوره‌های آموزشی.',
                        'url' => '/#books',
                        'image' => '/banner.png',
                    ],
                    'columns' => [
                        [
                            'title' => 'دسته‌بندی',
                            'links' => [
                                [
                                    'label' => 'آموزش نرم‌افزار',
                                    'url' => '/#books',
                                    'description' => 'راهنمای گام‌به‌گام',
                                ],
                                [
                                    'label' => 'مرجع مهندسی',
                                    'url' => '/#books',
                                    'description' => 'فرمول‌ها و مثال‌های کاربردی',
                                ],
                                [
                                    'label' => 'پکیج دوره + کتاب',
                                    'url' => '/#books',
                                    'description' => 'خرید هم‌زمان با ثبت‌نام',
                                ],
                            ],
                        ],
                        [
                            'title' => 'دسترسی',
                            'links' => [
                                [
                                    'label' => 'دانلود امن',
                                    'url' => '/student/dashboard',
                                    'description' => 'از پنل دانشجو',
                                    'visibility' => 'auth',
                                ],
                                [
                                    'label' => 'خرید و دانلود',
                                    'url' => '/#books',
                                    'description' => 'پرداخت آنلاین با زرین‌پال',
                                ],
                            ],
                        ],
                    ],
                ],
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
                                    'roles' => ['instructor', 'admin'],
                                ],
                                [
                                    'label' => 'پنل ادمین',
                                    'url' => '/admin',
                                    'description' => 'مدیریت محتوا و سفارش‌ها',
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

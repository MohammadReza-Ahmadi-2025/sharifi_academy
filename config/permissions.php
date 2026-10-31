<?php

return [

    /*
    |--------------------------------------------------------------------------
    | کاتالوگ دسترسی‌ها (برای UI نقش‌ها و sync دیتابیس)
    |--------------------------------------------------------------------------
    |
    | کلید آرایه = نام پرمیشن Spatie (guard: web)
    |
    */
    'groups' => [
        'admin' => [
            'label' => 'پنل مدیریت',
            'permissions' => [
                'admin-panel-access' => 'ورود به پنل ادمین',
                'admin-dashboard-view' => 'مشاهده داشبورد',
            ],
        ],
        'roles' => [
            'label' => 'نقش‌ها و دسترسی‌ها',
            'permissions' => [
                'roles-index' => 'مشاهده لیست نقش‌ها',
                'roles-create' => 'ایجاد نقش',
                'roles-update' => 'ویرایش نقش و دسترسی‌ها',
                'roles-delete' => 'حذف نقش',
            ],
        ],
        'users' => [
            'label' => 'کاربران',
            'permissions' => [
                'users-index' => 'مشاهده لیست کاربران',
                'users-create' => 'ایجاد کاربر',
                'users-update' => 'ویرایش کاربر',
                'users-delete' => 'حذف کاربر',
            ],
        ],
        'activity_logs' => [
            'label' => 'گزارش فعالیت',
            'permissions' => [
                'activity-logs-index' => 'لیست گزارش فعالیت',
                'activity-logs-show' => 'جزئیات گزارش فعالیت',
            ],
        ],
        'instructor_panel' => [
            'label' => 'پنل استاد',
            'permissions' => [
                'instructor-panel-access' => 'ورود به پنل استاد',
                'instructor-resume-manage' => 'مدیریت رزومه شخصی',
            ],
        ],
        'student_panel' => [
            'label' => 'پنل دانشجو',
            'permissions' => [
                'student-panel-access' => 'ورود به پنل دانشجو',
            ],
        ],
        'courses' => [
            'label' => 'دوره‌ها',
            'permissions' => [
                'courses-index' => 'مشاهده لیست دوره‌ها',
                'courses-create' => 'ایجاد دوره',
                'courses-update' => 'ویرایش دوره',
                'courses-delete' => 'حذف دوره',
            ],
        ],
        'instructor_moderation' => [
            'label' => 'تایید محتوای اساتید',
            'permissions' => [
                'instructor-moderation-index' => 'مشاهده درخواست‌های تایید',
                'instructor-moderation-update' => 'تایید یا رد دوره و رزومه استاد',
            ],
        ],
        'course_classes' => [
            'label' => 'کلاس‌های دوره',
            'permissions' => [
                'course-classes-index' => 'مشاهده کلاس‌های دوره',
                'course-classes-create' => 'ایجاد کلاس',
                'course-classes-update' => 'ویرایش کلاس',
                'course-classes-delete' => 'حذف کلاس',
            ],
        ],
        'class_sessions' => [
            'label' => 'جلسات کلاس',
            'permissions' => [
                'class-sessions-index' => 'مشاهده جلسات',
                'class-sessions-create' => 'ایجاد جلسه',
                'class-sessions-update' => 'ویرایش جلسه',
                'class-sessions-delete' => 'حذف جلسه',
            ],
        ],
        'enrollments' => [
            'label' => 'ثبت‌نام کلاس',
            'permissions' => [
                'enrollments-index' => 'مشاهده ثبت‌نام‌ها',
                'enrollments-create' => 'ثبت‌نام دستی',
                'enrollments-update' => 'ویرایش ثبت‌نام',
                'enrollments-delete' => 'حذف ثبت‌نام',
            ],
        ],
        'adobe_rooms' => [
            'label' => 'اتاق ادوبی',
            'permissions' => [
                'adobe-rooms-index' => 'مشاهده اتاق‌های ادوبی',
                'adobe-rooms-create' => 'ایجاد اتاق ادوبی',
                'adobe-rooms-update' => 'ویرایش اتاق ادوبی',
                'adobe-rooms-delete' => 'حذف اتاق ادوبی',
            ],
        ],
        'adobe_recordings' => [
            'label' => 'ضبط ادوبی',
            'permissions' => [
                'adobe-recordings-index' => 'مشاهده ضبط‌ها',
                'adobe-recordings-create' => 'ایجاد ضبط',
                'adobe-recordings-update' => 'ویرایش ضبط',
                'adobe-recordings-delete' => 'حذف ضبط',
            ],
        ],
        'session_attendances' => [
            'label' => 'حضور و غیاب جلسه',
            'permissions' => [
                'session-attendances-index' => 'مشاهده حضور و غیاب',
                'session-attendances-create' => 'ثبت حضور و غیاب',
                'session-attendances-update' => 'ویرایش حضور و غیاب',
                'session-attendances-delete' => 'حذف رکورد حضور',
            ],
        ],
        'quizzes' => [
            'label' => 'آزمون‌ها',
            'permissions' => [
                'quizzes-index' => 'مشاهده آزمون‌ها',
                'quizzes-create' => 'ایجاد آزمون',
                'quizzes-update' => 'ویرایش آزمون',
                'quizzes-delete' => 'حذف آزمون',
            ],
        ],
        'certificates' => [
            'label' => 'گواهینامه‌ها',
            'permissions' => [
                'certificates-issue' => 'صدور گواهینامه',
            ],
        ],
        'catalog_categories' => [
            'label' => 'دسته‌بندی کاتالوگ',
            'permissions' => [
                'catalog-categories-index' => 'مشاهده دسته‌بندی‌ها',
                'catalog-categories-create' => 'ایجاد دسته‌بندی',
                'catalog-categories-update' => 'ویرایش دسته‌بندی',
                'catalog-categories-delete' => 'حذف دسته‌بندی',
            ],
        ],
        'catalog_brands' => [
            'label' => 'برندها',
            'permissions' => [
                'catalog-brands-index' => 'مشاهده برندها',
                'catalog-brands-create' => 'ایجاد برند',
                'catalog-brands-update' => 'ویرایش برند',
                'catalog-brands-delete' => 'حذف برند',
            ],
        ],
        'catalog_attributes' => [
            'label' => 'ویژگی‌های محصول',
            'permissions' => [
                'catalog-attributes-index' => 'مشاهده ویژگی‌ها',
                'catalog-attributes-create' => 'ایجاد ویژگی',
                'catalog-attributes-update' => 'ویرایش ویژگی',
                'catalog-attributes-delete' => 'حذف ویژگی',
            ],
        ],
        'catalog_products' => [
            'label' => 'محصولات',
            'permissions' => [
                'catalog-products-index' => 'مشاهده محصولات',
                'catalog-products-create' => 'ایجاد محصول',
                'catalog-products-update' => 'ویرایش محصول',
                'catalog-products-delete' => 'حذف محصول',
            ],
        ],
        'catalog_warehouses' => [
            'label' => 'انبارها',
            'permissions' => [
                'catalog-warehouses-index' => 'مشاهده انبارها',
                'catalog-warehouses-create' => 'ایجاد انبار',
                'catalog-warehouses-update' => 'ویرایش انبار',
                'catalog-warehouses-delete' => 'حذف انبار',
            ],
        ],
        'catalog_inventory' => [
            'label' => 'موجودی انبار',
            'permissions' => [
                'catalog-inventory-index' => 'مشاهده موجودی',
                'catalog-inventory-adjust' => 'تعدیل موجودی',
            ],
        ],
        'locations' => [
            'label' => 'استان و شهر',
            'permissions' => [
                'locations-index' => 'مشاهده استان‌ها و شهرها',
                'locations-update' => 'ویرایش استان‌ها و شهرها',
            ],
        ],
        'files' => [
            'label' => 'فایل‌ها',
            'permissions' => [
                'files-index' => 'مشاهده فایل‌ها',
                'files-upload' => 'آپلود فایل',
                'files-delete' => 'حذف فایل',
            ],
        ],
        'skills' => [
            'label' => 'مهارت‌ها',
            'permissions' => [
                'skills-index' => 'مشاهده مهارت‌ها',
                'skills-create' => 'ایجاد مهارت',
                'skills-update' => 'ویرایش مهارت',
                'skills-delete' => 'حذف مهارت',
            ],
        ],
        'socials' => [
            'label' => 'شبکه‌های اجتماعی',
            'permissions' => [
                'socials-index' => 'مشاهده شبکه‌های اجتماعی',
                'socials-create' => 'ایجاد آیتم',
                'socials-update' => 'ویرایش آیتم',
                'socials-delete' => 'حذف آیتم',
            ],
        ],
        'comments' => [
            'label' => 'نظرات',
            'permissions' => [
                'comments-index' => 'مشاهده نظرات',
                'comments-create' => 'ثبت نظر دستی',
                'comments-update' => 'ویرایش و تأیید نظر',
                'comments-delete' => 'حذف نظر',
            ],
        ],
        'blog_posts' => [
            'label' => 'پست‌های وبلاگ',
            'permissions' => [
                'blog-posts-index' => 'مشاهده پست‌ها',
                'blog-posts-create' => 'ایجاد پست',
                'blog-posts-update' => 'ویرایش پست',
                'blog-posts-delete' => 'حذف پست',
            ],
        ],
        'pages' => [
            'label' => 'صفحات ثابت',
            'permissions' => [
                'pages-index' => 'مشاهده صفحات',
                'pages-create' => 'ایجاد صفحه',
                'pages-update' => 'ویرایش صفحه',
                'pages-delete' => 'حذف صفحه',
            ],
        ],
        'blog_categories' => [
            'label' => 'دسته‌های وبلاگ',
            'permissions' => [
                'blog-categories-index' => 'مشاهده دسته‌های وبلاگ',
                'blog-categories-create' => 'ایجاد دسته وبلاگ',
                'blog-categories-update' => 'ویرایش دسته وبلاگ',
                'blog-categories-delete' => 'حذف دسته وبلاگ',
            ],
        ],
        'stories' => [
            'label' => 'استوری‌ها',
            'permissions' => [
                'stories-index' => 'مشاهده استوری‌ها',
                'stories-create' => 'ایجاد استوری',
                'stories-update' => 'ویرایش استوری',
                'stories-moderate' => 'نظارت و حذف استوری',
            ],
        ],
        'home' => [
            'label' => 'صفحه اصلی',
            'permissions' => [
                'home-sections-index' => 'مشاهده سکشن‌های صفحه اصلی',
                'home-sections-create' => 'ایجاد سکشن صفحه اصلی',
                'home-sections-update' => 'ویرایش سکشن‌های صفحه اصلی',
                'home-sections-delete' => 'حذف سکشن صفحه اصلی',
            ],
        ],
        'site_settings' => [
            'label' => 'تنظیمات سایت',
            'permissions' => [
                'site-settings-index' => 'مشاهده تنظیمات سایت',
                'site-settings-update' => 'ویرایش تنظیمات سایت',
            ],
        ],
        'menus' => [
            'label' => 'منوهای سایت',
            'permissions' => [
                'menus-index' => 'مشاهده منوها',
                'menus-create' => 'ایجاد منو',
                'menus-update' => 'ویرایش منو',
                'menus-delete' => 'حذف منو',
            ],
        ],
        'visits' => [
            'label' => 'بازدیدها',
            'permissions' => [
                'visits-index' => 'مشاهده بازدیدها',
            ],
        ],
    ],

    'role_labels' => [
        'super-admin' => 'مدیر ارشد',
        'admin' => 'مدیر',
        'instructor' => 'مدرس',
        'student' => 'دانش‌آموز',
    ],

    /** نقش‌های پیش‌فرض سامانه — حذف ممنوع */
    'system_roles' => [
        'super-admin',
        'admin',
        'instructor',
        'student',
    ],

];

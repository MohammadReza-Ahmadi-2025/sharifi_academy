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

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | منوی پنل ادمین
    |--------------------------------------------------------------------------
    |
    | permission: نام پرمیشن Spatie (اختیاری)
    | roles: نقش‌های مجاز اگر پرمیشن نباشد (اختیاری)
    |
    */
    'navigation' => [
        [
            'id' => 'dashboard',
            'label' => 'داشبورد',
            'href' => '/admin',
            'permission' => 'admin-dashboard-view',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'users',
            'label' => 'کاربران',
            'href' => '/admin/users',
            'permission' => 'users-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'roles',
            'label' => 'نقش‌ها و دسترسی‌ها',
            'href' => '/admin/roles',
            'permission' => 'roles-index',
            'roles' => ['super-admin', 'admin'],
        ],
    ],

];

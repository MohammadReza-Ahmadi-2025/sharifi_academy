<?php

return [

    /*
    |--------------------------------------------------------------------------
    | منوی پنل ادمین
    |--------------------------------------------------------------------------
    |
    | permission: نام پرمیشن Spatie (اختیاری)
    | permissions_any: اگر یکی از پرمیشن‌ها باشد منو نمایش داده می‌شود
    | roles: نقش‌های مجاز اگر پرمیشن نباشد (اختیاری)
    | icon: کلید آیکون در admin-nav-icons (پیش‌فرض: id)
    |
    */
    'navigation' => [
        [
            'id' => 'dashboard',
            'label' => 'داشبورد',
            'href' => '/admin',
            'icon' => 'dashboard',
            'permission' => 'admin-dashboard-view',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'users',
            'label' => 'کاربران',
            'href' => '/admin/users',
            'icon' => 'users',
            'permission' => 'users-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'roles',
            'label' => 'نقش‌ها و دسترسی‌ها',
            'href' => '/admin/roles',
            'icon' => 'roles',
            'permission' => 'roles-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'courses',
            'label' => 'دوره‌ها',
            'href' => '/admin/courses',
            'icon' => 'courses',
            'permission' => 'courses-index',
            'roles' => ['super-admin', 'admin', 'instructor'],
        ],
        [
            'id' => 'course-categories',
            'label' => 'دسته‌بندی دوره‌ها',
            'href' => '/admin/categories?type=course',
            'icon' => 'categories',
            'permission' => 'catalog-categories-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'skills',
            'label' => 'مهارت‌ها',
            'href' => '/admin/skills',
            'icon' => 'skills',
            'permission' => 'skills-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'locations',
            'label' => 'استان و شهر',
            'href' => '/admin/locations',
            'icon' => 'locations',
            'permission' => 'locations-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'socials',
            'label' => 'شبکه‌های اجتماعی',
            'href' => '/admin/socials',
            'icon' => 'socials',
            'permission' => 'socials-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'comments',
            'label' => 'نظرات',
            'href' => '/admin/comments',
            'icon' => 'comments',
            'permission' => 'comments-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'blog-posts',
            'label' => 'پست‌ها',
            'href' => '/admin/posts',
            'icon' => 'blog-posts',
            'permission' => 'blog-posts-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'stories',
            'label' => 'استوری‌ها',
            'href' => '/admin/stories',
            'icon' => 'stories',
            'permission' => 'stories-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'home-sections',
            'label' => 'صفحه اصلی',
            'href' => '/admin/home-sections',
            'icon' => 'home-sections',
            'permission' => 'home-sections-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'menus',
            'label' => 'منوهای سایت',
            'href' => '/admin/menus',
            'icon' => 'menus',
            'permission' => 'menus-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'visits',
            'label' => 'بازدیدها',
            'href' => '/admin/visits',
            'icon' => 'visits',
            'permission' => 'visits-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'site-settings',
            'label' => 'تنظیمات سایت',
            'href' => '/admin/settings',
            'icon' => 'site-settings',
            'permission' => 'site-settings-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'activity-logs',
            'label' => 'گزارش فعالیت',
            'href' => '/admin/activity-logs',
            'icon' => 'activity-logs',
            'permission' => 'activity-logs-index',
            'roles' => ['super-admin', 'admin'],
        ],
    ],

];

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
            'id' => 'categories',
            'label' => 'دسته‌بندی‌ها',
            'href' => '/admin/categories',
            'icon' => 'categories',
            'permissions_any' => [
                'catalog-categories-index',
                'blog-categories-index',
            ],
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'catalog-products',
            'label' => 'محصولات',
            'href' => '/admin/catalog/products',
            'icon' => 'catalog-products',
            'permission' => 'catalog-products-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'catalog-brands',
            'label' => 'برندها',
            'href' => '/admin/catalog/brands',
            'icon' => 'catalog-brands',
            'permission' => 'catalog-brands-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'catalog-warehouses',
            'label' => 'انبارها',
            'href' => '/admin/catalog/warehouses',
            'icon' => 'catalog-warehouses',
            'permission' => 'catalog-warehouses-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'catalog-inventory',
            'label' => 'موجودی انبار',
            'href' => '/admin/catalog/inventory',
            'icon' => 'catalog-inventory',
            'permission' => 'catalog-inventory-index',
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
            'id' => 'files',
            'label' => 'فایل‌ها',
            'href' => '/admin/files',
            'icon' => 'files',
            'permission' => 'files-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'session-attendances',
            'label' => 'حضور و غیاب',
            'href' => '/admin/session-attendances',
            'icon' => 'session-attendances',
            'permission' => 'session-attendances-index',
            'roles' => ['super-admin', 'admin', 'instructor'],
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
            'label' => 'پست‌های وبلاگ',
            'href' => '/admin/blog/posts',
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
            'label' => 'سکشن‌های خانه',
            'href' => '/admin/home-sections',
            'icon' => 'home-sections',
            'permission' => 'home-sections-index',
            'roles' => ['super-admin', 'admin'],
        ],
        [
            'id' => 'home-banners',
            'label' => 'بنرهای خانه',
            'href' => '/admin/home-banners',
            'icon' => 'home-banners',
            'permission' => 'home-banners-index',
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

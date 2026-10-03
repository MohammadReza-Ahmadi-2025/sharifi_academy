<?php

use App\Models\User;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Comments\Models\Comment;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Roles\Models\Role;
use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Social\Models\Social;
use App\Modules\Stories\Models\Story;

return [

    /*
    |--------------------------------------------------------------------------
    | مدل‌هایی که تغییراتشان ثبت می‌شود (کاربران دارای نقش)
    |--------------------------------------------------------------------------
    */
    'models' => [
        User::class,
        Role::class,
        Brand::class,
        Category::class,
        Product::class,
        ProductVariant::class,
        Course::class,
        CourseClass::class,
        ClassSession::class,
        Enrollment::class,
        Post::class,
        Comment::class,
        Social::class,
        SiteSetting::class,
        Story::class,
    ],

    'hidden_attributes' => [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ],

];

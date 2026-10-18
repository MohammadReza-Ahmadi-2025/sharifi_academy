<?php

use App\Models\User;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Attribute;
use App\Modules\Catalog\Models\AttributeValue;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Catalog\Models\Warehouse;
use App\Modules\Comments\Models\Comment;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Home\Models\Banner;
use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionItem;
use App\Modules\Home\Models\HomeSectionTab;
use App\Modules\Locations\Models\ProvinceCity;
use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Models\MenuItem;
use App\Modules\Pages\Models\Page;
use App\Modules\Profile\Models\Skill;
use App\Modules\Roles\Models\Permission;
use App\Modules\Roles\Models\Role;
use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Social\Models\Social;
use App\Modules\Stories\Models\Story;

return [

    /*
    |--------------------------------------------------------------------------
    | مدل‌هایی که تغییراتشان ثبت می‌شود (کاربران با دسترسی پنل ادمین)
    |--------------------------------------------------------------------------
    */
    'models' => [
        User::class,
        Role::class,
        Permission::class,
        Brand::class,
        Category::class,
        Product::class,
        ProductVariant::class,
        Attribute::class,
        AttributeValue::class,
        Warehouse::class,
        Course::class,
        CourseClass::class,
        ClassSession::class,
        Enrollment::class,
        Post::class,
        Page::class,
        Comment::class,
        Social::class,
        SiteSetting::class,
        Story::class,
        Skill::class,
        ProvinceCity::class,
        Menu::class,
        MenuItem::class,
        HomeSection::class,
        HomeSectionTab::class,
        HomeSectionItem::class,
        Banner::class,
        AdobeRoom::class,
        AdobeRecording::class,
    ],

    'hidden_attributes' => [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ],

];

<?php

use App\Modules\Activity\Http\Controllers\Admin\ActivityLogController;
use App\Modules\Admin\Http\Controllers\DashboardController;
use App\Modules\AdobeConnect\Http\Controllers\Admin\AdobeRecordingController;
use App\Modules\AdobeConnect\Http\Controllers\Admin\AdobeRoomController;
use App\Modules\Blog\Http\Controllers\Admin\PostController as BlogPostController;
use App\Modules\Catalog\Http\Controllers\Admin\BrandController;
use App\Modules\Catalog\Http\Controllers\Admin\CategoryController;
use App\Modules\Catalog\Http\Controllers\Admin\InventoryController;
use App\Modules\Catalog\Http\Controllers\Admin\ProductController as CatalogProductController;
use App\Modules\Catalog\Http\Controllers\Admin\ProductVariantController as CatalogProductVariantController;
use App\Modules\Catalog\Http\Controllers\Admin\WarehouseController;
use App\Modules\Comments\Http\Controllers\Admin\CommentController;
use App\Modules\Courses\Http\Controllers\Admin\ClassSessionController;
use App\Modules\Courses\Http\Controllers\Admin\CourseClassController;
use App\Modules\Courses\Http\Controllers\Admin\CourseController;
use App\Modules\Courses\Http\Controllers\Admin\EnrollmentController;
use App\Modules\Courses\Http\Controllers\Admin\SessionAttendanceController;
use App\Modules\Files\Http\Controllers\Admin\FileController;
use App\Modules\Home\Http\Controllers\Admin\BannerController;
use App\Modules\Home\Http\Controllers\Admin\HomeSectionController;
use App\Modules\Menu\Http\Controllers\Admin\MenuController;
use App\Modules\Locations\Http\Controllers\Admin\ProvinceCityController;
use App\Modules\Profile\Http\Controllers\Admin\SkillController;
use App\Modules\Profile\Http\Controllers\ProfileController;
use App\Modules\Roles\Http\Controllers\RoleController;
use App\Modules\Settings\Http\Controllers\Admin\SiteSettingController;
use App\Modules\Social\Http\Controllers\Admin\SocialController;
use App\Modules\Stories\Http\Controllers\Admin\StoryController as AdminStoryController;
use App\Modules\Users\Http\Controllers\UserController;
use App\Modules\Visits\Http\Controllers\Admin\VisitController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin.panel'])
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        Route::middleware('permission:users-index')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
        });

        Route::middleware('permission:users-create')->group(function () {
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
        });

        Route::middleware('permission:users-update')->group(function () {
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        });

        Route::middleware('permission:users-delete')->group(function () {
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:roles-index')->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        });

        Route::middleware('permission:roles-create')->group(function () {
            Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        });

        Route::middleware('permission:roles-update')->group(function () {
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });

        Route::middleware('permission:roles-delete')->group(function () {
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        });

        Route::middleware('permission:courses-index')->group(function () {
            Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
        });

        Route::middleware('permission:courses-create')->group(function () {
            Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
            Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
        });

        Route::middleware('permission:courses-index')->group(function () {
            Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');
        });

        Route::middleware('permission:courses-update')->group(function () {
            Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
            Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        });

        Route::middleware('permission:courses-delete')->group(function () {
            Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        });

        Route::middleware('permission:course-classes-index')->group(function () {
            Route::get('courses/{course}/classes', [CourseClassController::class, 'index'])->name('courses.classes.index');
        });

        Route::middleware('permission:course-classes-create')->group(function () {
            Route::get('courses/{course}/classes/create', [CourseClassController::class, 'create'])->name('courses.classes.create');
            Route::post('courses/{course}/classes', [CourseClassController::class, 'store'])->name('courses.classes.store');
        });

        Route::middleware('permission:course-classes-update')->group(function () {
            Route::get('courses/{course}/classes/{courseClass}/edit', [CourseClassController::class, 'edit'])->name('courses.classes.edit');
            Route::put('courses/{course}/classes/{courseClass}', [CourseClassController::class, 'update'])->name('courses.classes.update');
        });

        Route::middleware('permission:course-classes-delete')->group(function () {
            Route::delete('courses/{course}/classes/{courseClass}', [CourseClassController::class, 'destroy'])->name('courses.classes.destroy');
        });

        Route::middleware('permission:enrollments-create')->group(function () {
            Route::post('courses/{course}/classes/{courseClass}/enrollments', [EnrollmentController::class, 'store'])->name('courses.classes.enrollments.store');
        });

        Route::middleware('permission:enrollments-update')->group(function () {
            Route::put('courses/{course}/classes/{courseClass}/enrollments/{enrollment}', [EnrollmentController::class, 'update'])->name('courses.classes.enrollments.update');
        });

        Route::middleware('permission:enrollments-delete')->group(function () {
            Route::delete('courses/{course}/classes/{courseClass}/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('courses.classes.enrollments.destroy');
        });

        Route::middleware('permission:class-sessions-index')->group(function () {
            Route::get('courses/{course}/classes/{courseClass}/sessions', [ClassSessionController::class, 'index'])->name('courses.classes.sessions.index');
        });

        Route::middleware('permission:class-sessions-create')->group(function () {
            Route::get('courses/{course}/classes/{courseClass}/sessions/create', [ClassSessionController::class, 'create'])->name('courses.classes.sessions.create');
            Route::post('courses/{course}/classes/{courseClass}/sessions', [ClassSessionController::class, 'store'])->name('courses.classes.sessions.store');
        });

        Route::middleware('permission:class-sessions-update')->group(function () {
            Route::get('courses/{course}/classes/{courseClass}/sessions/{classSession}/edit', [ClassSessionController::class, 'edit'])->name('courses.classes.sessions.edit');
            Route::put('courses/{course}/classes/{courseClass}/sessions/{classSession}', [ClassSessionController::class, 'update'])->name('courses.classes.sessions.update');
        });

        Route::middleware('permission:class-sessions-delete')->group(function () {
            Route::delete('courses/{course}/classes/{courseClass}/sessions/{classSession}', [ClassSessionController::class, 'destroy'])->name('courses.classes.sessions.destroy');
        });

        Route::middleware('permission:adobe-rooms-create')->group(function () {
            Route::post('courses/{course}/classes/{courseClass}/adobe-rooms', [AdobeRoomController::class, 'storeForClass'])->name('courses.classes.adobe-rooms.store');
            Route::post('courses/{course}/classes/{courseClass}/sessions/{classSession}/adobe-rooms', [AdobeRoomController::class, 'storeForSession'])->name('courses.classes.sessions.adobe-rooms.store');
        });

        Route::middleware('permission:adobe-rooms-delete')->group(function () {
            Route::delete('courses/{course}/classes/{courseClass}/adobe-rooms/{adobeRoom}', [AdobeRoomController::class, 'destroyForClass'])->name('courses.classes.adobe-rooms.destroy');
        });

        Route::middleware('permission:adobe-recordings-create')->group(function () {
            Route::post('courses/{course}/classes/{courseClass}/sessions/{classSession}/adobe-recordings', [AdobeRecordingController::class, 'store'])->name('courses.classes.sessions.adobe-recordings.store');
        });

        Route::middleware('permission:adobe-recordings-delete')->group(function () {
            Route::delete('courses/{course}/classes/{courseClass}/sessions/{classSession}/adobe-recordings/{adobeRecording}', [AdobeRecordingController::class, 'destroy'])->name('courses.classes.sessions.adobe-recordings.destroy');
        });

        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::redirect('catalog/categories', '/admin/categories?type=product');
        Route::redirect('catalog/categories/create', '/admin/categories/create?type=product');
        Route::redirect('blog/categories', '/admin/categories?type=blog');
        Route::redirect('blog/categories/create', '/admin/categories/create?type=blog');

        Route::middleware('permission:catalog-brands-index')->group(function () {
            Route::get('catalog/brands', [BrandController::class, 'index'])->name('catalog.brands.index');
        });

        Route::middleware('permission:catalog-brands-create')->group(function () {
            Route::get('catalog/brands/create', [BrandController::class, 'create'])->name('catalog.brands.create');
            Route::post('catalog/brands', [BrandController::class, 'store'])->name('catalog.brands.store');
        });

        Route::middleware('permission:catalog-brands-update')->group(function () {
            Route::get('catalog/brands/{brand}/edit', [BrandController::class, 'edit'])->name('catalog.brands.edit');
            Route::put('catalog/brands/{brand}', [BrandController::class, 'update'])->name('catalog.brands.update');
        });

        Route::middleware('permission:catalog-brands-delete')->group(function () {
            Route::delete('catalog/brands/{brand}', [BrandController::class, 'destroy'])->name('catalog.brands.destroy');
        });

        Route::middleware('permission:activity-logs-index')->group(function () {
            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        });

        Route::middleware('permission:activity-logs-show')->group(function () {
            Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
        });

        Route::middleware('permission:catalog-warehouses-index')->group(function () {
            Route::get('catalog/warehouses', [WarehouseController::class, 'index'])->name('catalog.warehouses.index');
        });
        Route::middleware('permission:catalog-warehouses-create')->group(function () {
            Route::get('catalog/warehouses/create', [WarehouseController::class, 'create'])->name('catalog.warehouses.create');
            Route::post('catalog/warehouses', [WarehouseController::class, 'store'])->name('catalog.warehouses.store');
        });
        Route::middleware('permission:catalog-warehouses-update')->group(function () {
            Route::get('catalog/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('catalog.warehouses.edit');
            Route::put('catalog/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('catalog.warehouses.update');
        });
        Route::middleware('permission:catalog-warehouses-delete')->group(function () {
            Route::delete('catalog/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('catalog.warehouses.destroy');
        });

        Route::middleware('permission:catalog-inventory-index')->group(function () {
            Route::get('catalog/inventory', [InventoryController::class, 'index'])->name('catalog.inventory.index');
        });
        Route::middleware('permission:catalog-inventory-adjust')->group(function () {
            Route::post('catalog/inventory/adjust', [InventoryController::class, 'adjust'])->name('catalog.inventory.adjust');
        });

        Route::middleware('permission:skills-index')->group(function () {
            Route::get('skills', [SkillController::class, 'index'])->name('skills.index');
        });
        Route::middleware('permission:skills-create')->group(function () {
            Route::get('skills/create', [SkillController::class, 'create'])->name('skills.create');
            Route::post('skills', [SkillController::class, 'store'])->name('skills.store');
        });
        Route::middleware('permission:skills-update')->group(function () {
            Route::get('skills/{skill}/edit', [SkillController::class, 'edit'])->name('skills.edit');
            Route::put('skills/{skill}', [SkillController::class, 'update'])->name('skills.update');
        });
        Route::middleware('permission:skills-delete')->group(function () {
            Route::delete('skills/{skill}', [SkillController::class, 'destroy'])->name('skills.destroy');
        });

        Route::middleware('permission:locations-index')->group(function () {
            Route::get('locations', [ProvinceCityController::class, 'index'])->name('locations.index');
        });
        Route::middleware('permission:locations-update')->group(function () {
            Route::get('locations/{province}/edit', [ProvinceCityController::class, 'edit'])->name('locations.edit');
            Route::put('locations/{province}', [ProvinceCityController::class, 'update'])->name('locations.update');
        });

        Route::middleware('permission:files-index')->group(function () {
            Route::get('files', [FileController::class, 'index'])->name('files.index');
        });
        Route::middleware('permission:files-delete')->group(function () {
            Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
        });

        Route::middleware('permission:session-attendances-index')->group(function () {
            Route::get('session-attendances', [SessionAttendanceController::class, 'index'])->name('session-attendances.index');
            Route::get('session-attendances/{classSession}', [SessionAttendanceController::class, 'show'])->name('session-attendances.show');
        });
        Route::middleware('permission:session-attendances-update')->group(function () {
            Route::put('session-attendances/{classSession}', [SessionAttendanceController::class, 'sync'])->name('session-attendances.sync');
        });

        Route::middleware('permission:catalog-products-index')->group(function () {
            Route::get('catalog/products', [CatalogProductController::class, 'index'])->name('catalog.products.index');
        });

        Route::middleware('permission:catalog-products-create')->group(function () {
            Route::get('catalog/products/create', [CatalogProductController::class, 'create'])->name('catalog.products.create');
            Route::post('catalog/products', [CatalogProductController::class, 'store'])->name('catalog.products.store');
        });

        Route::middleware('permission:catalog-products-update')->group(function () {
            Route::get('catalog/products/{product}/edit', [CatalogProductController::class, 'edit'])->name('catalog.products.edit');
            Route::put('catalog/products/{product}', [CatalogProductController::class, 'update'])->name('catalog.products.update');
            Route::post('catalog/products/{product}/variants', [CatalogProductVariantController::class, 'store'])->name('catalog.products.variants.store');
            Route::put('catalog/products/{product}/variants/reorder', [CatalogProductVariantController::class, 'reorder'])->name('catalog.products.variants.reorder');
            Route::put('catalog/products/{product}/variants/{variant}', [CatalogProductVariantController::class, 'update'])->name('catalog.products.variants.update');
            Route::delete('catalog/products/{product}/variants/{variant}', [CatalogProductVariantController::class, 'destroy'])->name('catalog.products.variants.destroy');
        });

        Route::middleware('permission:catalog-products-delete')->group(function () {
            Route::delete('catalog/products/{product}', [CatalogProductController::class, 'destroy'])->name('catalog.products.destroy');
        });

        Route::middleware('permission:socials-index')->group(function () {
            Route::get('socials', [SocialController::class, 'index'])->name('socials.index');
        });

        Route::middleware('permission:socials-create')->group(function () {
            Route::get('socials/create', [SocialController::class, 'create'])->name('socials.create');
            Route::post('socials', [SocialController::class, 'store'])->name('socials.store');
        });

        Route::middleware('permission:socials-update')->group(function () {
            Route::get('socials/{social}/edit', [SocialController::class, 'edit'])->name('socials.edit');
            Route::put('socials/{social}', [SocialController::class, 'update'])->name('socials.update');
        });

        Route::middleware('permission:socials-delete')->group(function () {
            Route::delete('socials/{social}', [SocialController::class, 'destroy'])->name('socials.destroy');
        });

        Route::middleware('permission:comments-index')->group(function () {
            Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
        });

        Route::middleware('permission:comments-create')->group(function () {
            Route::get('comments/create', [CommentController::class, 'create'])->name('comments.create');
            Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
        });

        Route::middleware('permission:comments-update')->group(function () {
            Route::get('comments/{comment}/edit', [CommentController::class, 'edit'])->name('comments.edit');
            Route::put('comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
            Route::post('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
            Route::post('comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
            Route::post('comments/{comment}/reply', [CommentController::class, 'reply'])->name('comments.reply');
        });

        Route::middleware('permission:comments-delete')->group(function () {
            Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
        });

        Route::middleware('permission:blog-posts-index')->group(function () {
            Route::get('blog/posts', [BlogPostController::class, 'index'])->name('blog.posts.index');
        });

        Route::middleware('permission:blog-posts-create')->group(function () {
            Route::get('blog/posts/create', [BlogPostController::class, 'create'])->name('blog.posts.create');
            Route::post('blog/posts', [BlogPostController::class, 'store'])->name('blog.posts.store');
        });

        Route::middleware('permission:blog-posts-update')->group(function () {
            Route::get('blog/posts/{post}/edit', [BlogPostController::class, 'edit'])->name('blog.posts.edit');
            Route::put('blog/posts/{post}', [BlogPostController::class, 'update'])->name('blog.posts.update');
        });

        Route::middleware('permission:blog-posts-delete')->group(function () {
            Route::delete('blog/posts/{post}', [BlogPostController::class, 'destroy'])->name('blog.posts.destroy');
        });

        Route::middleware('permission:stories-index')->group(function () {
            Route::get('stories', [AdminStoryController::class, 'index'])->name('stories.index');
        });

        Route::middleware('permission:stories-moderate')->group(function () {
            Route::post('stories/{story}/remove', [AdminStoryController::class, 'remove'])->name('stories.remove');
        });

        Route::get('home-sections/search-items', [HomeSectionController::class, 'searchItems'])
            ->name('home-sections.search-items');

        Route::middleware('permission:home-sections-index')->group(function () {
            Route::get('home-sections', [HomeSectionController::class, 'index'])->name('home-sections.index');
        });

        Route::middleware('permission:home-sections-create')->group(function () {
            Route::get('home-sections/create', [HomeSectionController::class, 'create'])->name('home-sections.create');
            Route::post('home-sections', [HomeSectionController::class, 'store'])->name('home-sections.store');
        });

        Route::middleware('permission:home-sections-update')->group(function () {
            Route::put('home-sections/reorder', [HomeSectionController::class, 'reorder'])->name('home-sections.reorder');
            Route::get('home-sections/{home_section}/edit', [HomeSectionController::class, 'edit'])->name('home-sections.edit');
            Route::put('home-sections/{home_section}', [HomeSectionController::class, 'update'])->name('home-sections.update');
        });

        Route::middleware('permission:home-sections-delete')->group(function () {
            Route::delete('home-sections/{home_section}', [HomeSectionController::class, 'destroy'])->name('home-sections.destroy');
        });

        Route::middleware('permission:home-banners-index')->group(function () {
            Route::get('home-banners', [BannerController::class, 'index'])->name('home-banners.index');
        });

        Route::middleware('permission:home-banners-create')->group(function () {
            Route::get('home-banners/create', [BannerController::class, 'create'])->name('home-banners.create');
            Route::post('home-banners', [BannerController::class, 'store'])->name('home-banners.store');
        });

        Route::middleware('permission:home-banners-update')->group(function () {
            Route::get('home-banners/{banner}/edit', [BannerController::class, 'edit'])->name('home-banners.edit');
            Route::put('home-banners/{banner}', [BannerController::class, 'update'])->name('home-banners.update');
        });

        Route::middleware('permission:home-banners-delete')->group(function () {
            Route::delete('home-banners/{banner}', [BannerController::class, 'destroy'])->name('home-banners.destroy');
        });

        Route::middleware('permission:menus-index')->group(function () {
            Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
        });

        Route::middleware('permission:menus-create')->group(function () {
            Route::get('menus/create', [MenuController::class, 'create'])->name('menus.create');
            Route::post('menus', [MenuController::class, 'store'])->name('menus.store');
        });

        Route::middleware('permission:menus-update')->group(function () {
            Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
            Route::put('menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
        });

        Route::middleware('permission:menus-delete')->group(function () {
            Route::delete('menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
        });

        Route::middleware('permission:visits-index')->group(function () {
            Route::get('visits', [VisitController::class, 'index'])->name('visits.index');
        });

        Route::middleware('permission:site-settings-index')->group(function () {
            Route::get('settings', [SiteSettingController::class, 'index'])->name('settings.index');
        });

        Route::middleware('permission:site-settings-update')->group(function () {
            Route::get('settings/{siteSetting}/edit', [SiteSettingController::class, 'edit'])->name('settings.edit');
            Route::match(['put', 'post'], 'settings/{siteSetting}', [SiteSettingController::class, 'update'])->name('settings.update');
        });
    });

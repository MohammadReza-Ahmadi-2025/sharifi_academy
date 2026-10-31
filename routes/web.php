<?php

use App\Http\Controllers\Account\NotificationController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\RobotsController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\SiteSearchController;
use App\Modules\Assessments\Http\Controllers\Public\CertificateVerifyController;
use App\Modules\Auth\Http\Controllers\OtpRequestController;
use App\Modules\Auth\Http\Controllers\OtpVerifyController;
use App\Modules\Blog\Http\Controllers\Site\BlogCategoryController;
use App\Modules\Blog\Http\Controllers\Site\PostController as BlogPostController;
use App\Modules\Catalog\Http\Controllers\Site\CategoryController;
use App\Modules\Catalog\Models\Category;
use App\Modules\Comments\Http\Controllers\Site\CourseCommentController;
use App\Modules\Comments\Http\Controllers\Site\PostCommentController;
use App\Modules\Courses\Http\Controllers\Site\CourseController;
use App\Modules\Courses\Http\Controllers\Site\CourseEnrollmentController;
use App\Modules\Pages\Http\Controllers\Site\PageController as SitePageController;
use App\Modules\Profile\Http\Controllers\AccountProfileController;
use App\Modules\Profile\Http\Controllers\Site\InstructorResumeController;
use App\Modules\Stories\Http\Controllers\Site\StoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/certificates/verify/{code}', CertificateVerifyController::class)->name('certificates.verify');

Route::get('/search', [SiteSearchController::class, 'index'])->name('search.index');
Route::get('/search/autocomplete', [SiteSearchController::class, 'autocomplete'])
    ->name('search.autocomplete');

Route::redirect('/categories', '/');
Route::redirect('/courses/categories', '/');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/courses/categories/{category:slug}', fn (Category $category) => redirect()->route('categories.show', $category, 301));
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/blog', [BlogPostController::class, 'index'])->name('blog.index');
Route::get('/blog/categories/{category:slug}', [BlogCategoryController::class, 'show'])
    ->name('blog.categories.show');
Route::get('/blog/{post:slug}', [BlogPostController::class, 'show'])->name('blog.show');

Route::get('/pages/{page:slug}', [SitePageController::class, 'show'])->name('pages.show');

Route::get('/stories/feed', [StoryController::class, 'feed'])->name('stories.feed');

Route::get('/instructors/{user}', [InstructorResumeController::class, 'showPage'])
    ->name('instructors.show');
Route::get('/instructors/{user}/resume', [InstructorResumeController::class, 'show'])
    ->name('instructors.resume');

Route::middleware('auth')->group(function () {
    Route::post('/courses/{course:slug}/enrollments', [CourseEnrollmentController::class, 'store'])
        ->name('courses.enrollments.store');
    Route::post('/courses/{course:slug}/comments', [CourseCommentController::class, 'store'])
        ->name('courses.comments.store');
    Route::post('/blog/{post:slug}/comments', [PostCommentController::class, 'store'])
        ->name('blog.comments.store');
    Route::post('/stories/{story}/views', [StoryController::class, 'recordView'])->name('stories.views');
    Route::get('/profile', [AccountProfileController::class, 'edit'])->name('site.profile.edit');
    Route::put('/profile', [AccountProfileController::class, 'update'])->name('site.profile.update');

    Route::post('/account/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('account.notifications.read-all');
    Route::post('/account/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('account.notifications.read');
});

Route::middleware('guest')->group(function () {
    Route::redirect('/register', '/login', 301);
    Route::post('/register', fn () => redirect()->route('login'));
    Route::post('/auth/otp/request', OtpRequestController::class)->name('auth.otp.request');
    Route::post('/auth/otp/verify', OtpVerifyController::class)->name('auth.otp.verify');
});

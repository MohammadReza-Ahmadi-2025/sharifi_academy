<?php

use App\Http\Controllers\Public\HomeController;
use App\Modules\Catalog\Models\Category;
use App\Modules\Comments\Http\Controllers\Site\CourseCommentController;
use App\Modules\Catalog\Http\Controllers\Site\CategoryController;
use App\Modules\Courses\Http\Controllers\Site\CourseController;
use App\Modules\Auth\Http\Controllers\OtpRequestController;
use App\Modules\Auth\Http\Controllers\OtpVerifyController;
use App\Modules\Auth\Http\Controllers\PasswordLoginController;
use App\Modules\Profile\Http\Controllers\AccountProfileController;
use App\Modules\Stories\Http\Controllers\Site\StoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::redirect('/categories', '/');
Route::redirect('/courses/categories', '/');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/courses/categories/{category:slug}', fn (Category $category) => redirect()->route('categories.show', $category, 301));
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/stories/feed', [StoryController::class, 'feed'])->name('stories.feed');

Route::middleware('auth')->group(function () {
    Route::post('/courses/{course:slug}/comments', [CourseCommentController::class, 'store'])
        ->name('courses.comments.store');
    Route::post('/stories/{story}/views', [StoryController::class, 'recordView'])->name('stories.views');
    Route::get('/profile', [AccountProfileController::class, 'edit'])->name('site.profile.edit');
    Route::put('/profile', [AccountProfileController::class, 'update'])->name('site.profile.update');
    Route::put('/profile/password', [AccountProfileController::class, 'updatePassword'])->name('site.profile.password');
});

Route::middleware('guest')->group(function () {
    Route::redirect('/register', '/login', 301);
    Route::post('/register', fn () => redirect()->route('login'));
    Route::post('/auth/otp/request', OtpRequestController::class)->name('auth.otp.request');
    Route::post('/auth/otp/verify', OtpVerifyController::class)->name('auth.otp.verify');
    Route::post('/auth/password/login', PasswordLoginController::class)
        ->middleware('throttle:password-login')
        ->name('auth.password.login');
});

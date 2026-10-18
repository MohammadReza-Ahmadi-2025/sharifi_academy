<?php

use App\Modules\Courses\Http\Controllers\Student\AdobeJoinController;
use App\Modules\Courses\Http\Controllers\Student\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'student.panel'])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('classes/{courseClass}/sessions/{classSession}/adobe', AdobeJoinController::class)
            ->name('sessions.adobe');
    });

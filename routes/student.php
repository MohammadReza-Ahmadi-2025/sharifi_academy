<?php

use App\Modules\Courses\Http\Controllers\Student\DashboardController;
use App\Modules\Courses\Http\Controllers\Student\SessionJoinController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'student.panel'])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('classes/{courseClass}/sessions/{classSession}/join', SessionJoinController::class)
            ->name('sessions.join');

        Route::get('classes/{courseClass}/sessions/{classSession}/adobe', SessionJoinController::class)
            ->name('sessions.adobe');
    });

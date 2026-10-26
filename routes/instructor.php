<?php

use App\Modules\AdobeConnect\Http\Controllers\Admin\AdobeRecordingController;
use App\Modules\AdobeConnect\Http\Controllers\Admin\AdobeRoomController;
use App\Modules\AdobeConnect\Http\Controllers\Admin\AdobeSyncController;
use App\Modules\Courses\Http\Controllers\Admin\ClassSessionController;
use App\Modules\Courses\Http\Controllers\Admin\CourseClassController;
use App\Modules\Courses\Http\Controllers\Instructor\CourseController;
use App\Modules\Courses\Http\Controllers\Instructor\DashboardController;
use App\Modules\Courses\Http\Controllers\Instructor\SessionAttendanceController as InstructorSessionAttendanceController;
use App\Modules\Profile\Http\Controllers\Instructor\ResumeController;
use Illuminate\Support\Facades\Route;

Route::prefix('instructor')
    ->name('instructor.')
    ->middleware(['auth', 'instructor.panel'])
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::middleware('permission:courses-index')->group(function () {
            Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
        });

        Route::middleware('permission:courses-create')->group(function () {
            Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
            Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
        });

        Route::middleware('permission:courses-update')->group(function () {
            Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
            Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        });

        Route::middleware('permission:instructor-resume-manage')->group(function () {
            Route::get('resume', [ResumeController::class, 'index'])->name('resume.index');
            Route::post('resume/submit', [ResumeController::class, 'submitForReview'])->name('resume.submit');
            Route::put('resume/skills', [ResumeController::class, 'syncSkills'])->name('resume.skills.sync');
            Route::post('resume', [ResumeController::class, 'store'])->name('resume.store');
            Route::put('resume/{resume}', [ResumeController::class, 'update'])->name('resume.update');
            Route::delete('resume/{resume}', [ResumeController::class, 'destroy'])->name('resume.destroy');
        });

        Route::middleware('permission:course-classes-index')->group(function () {
            Route::get('courses/{course}/classes', [CourseClassController::class, 'index'])
                ->name('courses.classes.index');
        });

        Route::middleware('permission:course-classes-create')->group(function () {
            Route::get('courses/{course}/classes/create', [CourseClassController::class, 'create'])
                ->name('courses.classes.create');
            Route::post('courses/{course}/classes', [CourseClassController::class, 'store'])
                ->name('courses.classes.store');
        });

        Route::middleware('permission:course-classes-update')->group(function () {
            Route::get('courses/{course}/classes/{courseClass}/edit', [CourseClassController::class, 'edit'])
                ->name('courses.classes.edit');
            Route::put('courses/{course}/classes/{courseClass}', [CourseClassController::class, 'update'])
                ->name('courses.classes.update');
        });

        Route::middleware('permission:class-sessions-index')->group(function () {
            Route::get('courses/{course}/classes/{courseClass}/sessions', [ClassSessionController::class, 'index'])
                ->name('courses.classes.sessions.index');
        });

        Route::middleware('permission:class-sessions-create')->group(function () {
            Route::get('courses/{course}/classes/{courseClass}/sessions/create', [ClassSessionController::class, 'create'])
                ->name('courses.classes.sessions.create');
            Route::post('courses/{course}/classes/{courseClass}/sessions', [ClassSessionController::class, 'store'])
                ->name('courses.classes.sessions.store');
        });

        Route::middleware('permission:class-sessions-update')->group(function () {
            Route::get('courses/{course}/classes/{courseClass}/sessions/{classSession}/edit', [ClassSessionController::class, 'edit'])
                ->name('courses.classes.sessions.edit');
            Route::put('courses/{course}/classes/{courseClass}/sessions/{classSession}', [ClassSessionController::class, 'update'])
                ->name('courses.classes.sessions.update');
        });

        Route::middleware('permission:class-sessions-delete')->group(function () {
            Route::delete('courses/{course}/classes/{courseClass}/sessions/{classSession}', [ClassSessionController::class, 'destroy'])
                ->name('courses.classes.sessions.destroy');
        });

        Route::middleware('permission:adobe-rooms-create')->group(function () {
            Route::post('courses/{course}/classes/{courseClass}/adobe-rooms', [AdobeRoomController::class, 'storeForClass'])
                ->name('courses.classes.adobe-rooms.store');
            Route::post('courses/{course}/classes/{courseClass}/sessions/{classSession}/adobe-rooms', [AdobeRoomController::class, 'storeForSession'])
                ->name('courses.classes.sessions.adobe-rooms.store');
        });

        Route::middleware('permission:adobe-rooms-update')->group(function () {
            Route::post('courses/{course}/classes/{courseClass}/adobe-rooms/{adobeRoom}/sync', [AdobeSyncController::class, 'syncRoom'])
                ->name('courses.classes.adobe-rooms.sync');
            Route::post('courses/{course}/classes/{courseClass}/sessions/{classSession}/adobe-rooms/{adobeRoom}/sync', [AdobeSyncController::class, 'syncSessionRoom'])
                ->name('courses.classes.sessions.adobe-rooms.sync');
        });

        Route::middleware('permission:adobe-rooms-delete')->group(function () {
            Route::delete('courses/{course}/classes/{courseClass}/adobe-rooms/{adobeRoom}', [AdobeRoomController::class, 'destroyForClass'])
                ->name('courses.classes.adobe-rooms.destroy');
            Route::delete('courses/{course}/classes/{courseClass}/sessions/{classSession}/adobe-rooms/{adobeRoom}', [AdobeRoomController::class, 'destroyForSession'])
                ->name('courses.classes.sessions.adobe-rooms.destroy');
        });

        Route::middleware('permission:adobe-recordings-create')->group(function () {
            Route::post('courses/{course}/classes/{courseClass}/sessions/{classSession}/adobe-recordings', [AdobeRecordingController::class, 'store'])
                ->name('courses.classes.sessions.adobe-recordings.store');
            Route::post('courses/{course}/classes/{courseClass}/sessions/{classSession}/adobe-recordings/sync', [AdobeSyncController::class, 'syncSessionRecordings'])
                ->name('courses.classes.sessions.adobe-recordings.sync');
        });

        Route::middleware('permission:adobe-recordings-delete')->group(function () {
            Route::delete('courses/{course}/classes/{courseClass}/sessions/{classSession}/adobe-recordings/{adobeRecording}', [AdobeRecordingController::class, 'destroy'])
                ->name('courses.classes.sessions.adobe-recordings.destroy');
        });

        Route::middleware('permission:class-sessions-index')->group(function () {
            Route::get('session-attendances', [InstructorSessionAttendanceController::class, 'index'])
                ->name('session-attendances.index');
            Route::get('session-attendances/{classSession}', [InstructorSessionAttendanceController::class, 'show'])
                ->name('session-attendances.show');
        });
    });

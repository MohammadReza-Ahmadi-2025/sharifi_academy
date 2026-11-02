<?php

use App\Modules\Assessments\Http\Controllers\Student\CertificateController;
use App\Modules\Assessments\Http\Controllers\Student\QuizController;
use App\Modules\Courses\Http\Controllers\Student\AttendanceController;
use App\Modules\Courses\Http\Controllers\Student\CourseEnrollmentController;
use App\Modules\Courses\Http\Controllers\Student\DashboardController;
use App\Modules\Courses\Http\Controllers\Student\SessionController;
use App\Modules\Courses\Http\Controllers\Student\SessionJoinController;
use App\Modules\Blog\Http\Controllers\Student\PostController as StudentPostController;
use App\Modules\Profile\Http\Controllers\Student\ResumeController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'student.panel'])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('courses', [CourseEnrollmentController::class, 'index'])->name('courses.index');
        Route::get('courses/{enrollment}', [CourseEnrollmentController::class, 'show'])->name('courses.show');

        Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');

        Route::get('quizzes', [QuizController::class, 'index'])->name('quizzes.index');
        Route::get('quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
        Route::post('quizzes/{quiz}/attempts', [QuizController::class, 'store'])->name('quizzes.attempts.store');

        Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
        Route::post('courses/{course}/certificates/claim', [CertificateController::class, 'claim'])
            ->name('certificates.claim');

        Route::middleware('permission:student-resume-manage')->group(function () {
            Route::get('resume', [ResumeController::class, 'index'])->name('resume.index');
            Route::post('resume/submit', [ResumeController::class, 'submitForReview'])->name('resume.submit');
            Route::put('resume/skills', [ResumeController::class, 'syncSkills'])->name('resume.skills.sync');
            Route::post('resume', [ResumeController::class, 'store'])->name('resume.store');
            Route::put('resume/{resume}', [ResumeController::class, 'update'])->name('resume.update');
            Route::delete('resume/{resume}', [ResumeController::class, 'destroy'])->name('resume.destroy');
        });

        Route::middleware('permission:panel-blog-posts-manage')->group(function () {
            Route::get('posts', [StudentPostController::class, 'index'])->name('posts.index');
            Route::get('posts/create', [StudentPostController::class, 'create'])->name('posts.create');
            Route::post('posts', [StudentPostController::class, 'store'])->name('posts.store');
            Route::get('posts/{post:id}/edit', [StudentPostController::class, 'edit'])->name('posts.edit');
            Route::put('posts/{post:id}', [StudentPostController::class, 'update'])->name('posts.update');
            Route::delete('posts/{post:id}', [StudentPostController::class, 'destroy'])->name('posts.destroy');
        });

        Route::get('classes/{courseClass}/sessions/{classSession}/join', SessionJoinController::class)
            ->name('sessions.join');

        Route::get('classes/{courseClass}/sessions/{classSession}/adobe', SessionJoinController::class)
            ->name('sessions.adobe');
    });

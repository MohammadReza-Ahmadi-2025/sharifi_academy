<?php

use App\Http\Middleware\EnsureSiteNotInMaintenance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Modules\Admin\Http\Middleware\EnsureAdminPanelAccess;
use App\Modules\Courses\Http\Middleware\EnsureInstructorPanelAccess;
use App\Modules\Courses\Http\Middleware\EnsureStudentPanelAccess;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/instructor.php'));

            Route::middleware('web')
                ->group(base_path('routes/student.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnsureSiteNotInMaintenance::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'admin.panel' => EnsureAdminPanelAccess::class,
            'instructor.panel' => EnsureInstructorPanelAccess::class,
            'student.panel' => EnsureStudentPanelAccess::class,
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            if ($request->is('api/*')) {
                return true;
            }

            // در حالت debug، Inertia همان صفحهٔ خطای Laravel (Ignition) را در مودال نشان می‌دهد.
            if ($request->header('X-Inertia')) {
                return ! config('app.debug');
            }

            return $request->expectsJson();
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('cart:clear-expired-items')->everyFiveMinutes();
        $schedule->command('adobe-connect:auto-sync')->hourly()->withoutOverlapping();
    })
    ->create();

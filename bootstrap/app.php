<?php

use App\Http\Middleware\EnsureSiteNotInMaintenance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Modules\Admin\Http\Middleware\EnsureAdminPanelAccess;
use App\Modules\Courses\Http\Middleware\EnsureInstructorPanelAccess;
use App\Modules\Courses\Http\Middleware\EnsureStudentPanelAccess;
use App\Http\Middleware\EnsureCanonicalHost;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        // Hosting (DirectAdmin/cPanel) terminates TLS; without this, Secure cookies / CSRF break.
        $middleware->trustProxies(at: '*');

        // Logout CSRF failures leave remember-me cookies intact; excluding logout is a safe UX tradeoff.
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);

        $middleware->web(append: [
            EnsureCanonicalHost::class,
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
        // Hide existence of protected resources: guests and forbidden users both see "not found" (no login redirect).
        $exceptions->map(
            AuthenticationException::class,
            fn (AuthenticationException $e) => new NotFoundHttpException(previous: $e),
        );

        $exceptions->map(
            AuthorizationException::class,
            fn (AuthorizationException $e) => new NotFoundHttpException(previous: $e),
        );

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 403) {
                return null;
            }

            throw new NotFoundHttpException(previous: $e);
        });

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

        $exceptions->respond(function (Response $response, \Throwable $exception, Request $request) {
            // CSRF/session mismatch: keep the user on the same page with a toastable flash (not a dead 419 page).
            if ($response->getStatusCode() === 419) {
                return back()->with('error', 'نشست منقضی شده؛ لطفاً دوباره تلاش کنید.');
            }

            // Partial Inertia mutations: surface server errors as flash instead of a full error document.
            if (
                $request->header('X-Inertia')
                && in_array($response->getStatusCode(), [500, 503], true)
                && ! in_array($request->method(), ['GET', 'HEAD'], true)
                && ! config('app.debug')
            ) {
                return back()->with('error', 'خطای سرور در این بخش. لطفاً دوباره تلاش کنید.');
            }

            return $response;
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('cart:clear-expired-items')->everyFiveMinutes();
        $schedule->command('adobe-connect:auto-sync')->hourly()->withoutOverlapping();
    })
    ->create();

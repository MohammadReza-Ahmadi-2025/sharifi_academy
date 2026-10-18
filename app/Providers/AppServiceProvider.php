<?php

namespace App\Providers;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use App\Support\EnsureViteBuildOrHot;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        EnsureViteBuildOrHot::reconcile();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (?User $user, string $ability): ?bool {
            if ($user !== null && $user->hasRole('super-admin')) {
                return true;
            }

            return null;
        });

        Inertia::handleExceptionsUsing(function ($exceptionResponse) {
            $status = $exceptionResponse->statusCode();

            if (config('app.debug') && $status >= 500) {
                return null;
            }

            if (! in_array($status, [400, 401, 403, 404, 419, 429, 500, 503], true)) {
                return null;
            }

            return $exceptionResponse
                ->render('Errors/Show', ['status' => $status])
                ->withSharedData()
                ->usingMiddleware(HandleInertiaRequests::class);
        });
    }
}

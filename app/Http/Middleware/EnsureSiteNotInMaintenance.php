<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteNotInMaintenance
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! site_setting('maintenance_mode', false)) {
            return $next($request);
        }

        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        return Inertia::render('Public/Maintenance')
            ->toResponse($request)
            ->setStatusCode(503);
    }

    private function shouldBypass(Request $request): bool
    {
        if ($request->is('admin', 'admin/*')) {
            return true;
        }

        if ($request->is('login', 'logout', 'forgot-password', 'reset-password', 'reset-password/*', 'auth/*')) {
            return true;
        }

        if ($request->is('up', 'build/*', 'storage/*')) {
            return true;
        }

        return false;
    }
}

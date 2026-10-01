<?php

namespace App\Modules\Admin\Http\Middleware;

use App\Modules\Admin\Support\AdminNavigation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPanelAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if (! AdminNavigation::canAccessPanel($user)) {
            abort(403);
        }

        return $next($request);
    }
}

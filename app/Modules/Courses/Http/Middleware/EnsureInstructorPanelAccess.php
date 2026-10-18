<?php

namespace App\Modules\Courses\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstructorPanelAccess
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

        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        if ($user->hasRole('instructor') && $user->can('instructor-panel-access')) {
            return $next($request);
        }

        abort(403);
    }
}

<?php

namespace App\Modules\Courses\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentPanelAccess
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

        if ($user->hasRole('student') && $user->can('student-panel-access')) {
            return $next($request);
        }

        abort(403);
    }
}

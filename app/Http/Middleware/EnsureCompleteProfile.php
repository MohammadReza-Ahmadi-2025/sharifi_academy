<?php

namespace App\Http\Middleware;

use App\Modules\Profile\Support\UserProfileCompleteness;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompleteProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest('/login');
        }

        if (! UserProfileCompleteness::isComplete($user)) {
            return redirect()
                ->route('site.profile.edit')
                ->with('error', 'برای گذاشتن پست باید وارد حساب شوید و پروفایل را تکمیل کنید (نام، نام خانوادگی و شهر).');
        }

        return $next($request);
    }
}

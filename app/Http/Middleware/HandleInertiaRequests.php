<?php

namespace App\Http\Middleware;

use App\Modules\Admin\Support\AdminNavigation;
use App\Modules\Users\Support\UserAvatarMedia;
use App\Support\SiteNavigation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
            ],
            'navigation' => fn () => SiteNavigation::forUser($user),
            'adminPanel' => fn () => ($user && ($request->routeIs('admin.*') || $request->is('admin', 'admin/*')))
                ? ['navigation' => AdminNavigation::forUser($user)]
                : null,
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'family' => $user->family,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'avatar' => UserAvatarMedia::payload($user),
                    'roles' => $user->getRoleNames()->values()->all(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->hasSession() ? $request->session()->get('success') : null,
                'error' => fn () => $request->hasSession() ? $request->session()->get('error') : null,
            ],
            'url' => fn () => $request->url(),
        ];
    }
}

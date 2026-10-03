<?php

namespace App\Http\Middleware;

use App\Modules\Admin\Support\AdminNavigation;
use App\Modules\Cart\Models\Cart;
use App\Modules\Menu\Services\SiteMenuService;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Modules\Users\Support\UserAvatarMedia;
use App\Support\SiteMenuCategoryService;
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
            'menuCategories' => fn () => app(SiteMenuCategoryService::class)->productRootsForHeader(),
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
            'cartQty' => fn () => Cart::currentQty(),
            'siteSettings' => fn () => app(SiteSettingsService::class)->autoload(),
            'siteMenus' => fn () => app(SiteMenuService::class)->groupedByLocation(),
            'url' => fn () => $request->url(),
        ];
    }
}

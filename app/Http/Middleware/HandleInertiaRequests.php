<?php

namespace App\Http\Middleware;

use App\Modules\Admin\Support\AdminNavigation;
use App\Modules\Cart\Models\Cart;
use App\Modules\Menu\Services\SiteMenuService;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Modules\Settings\Support\SiteSettingsInertiaPayload;
use App\Modules\Social\Services\SiteSocialService;
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
        $settings = app(SiteSettingsService::class);
        $siteName = (string) ($settings->get('site_name') ?: config('app.name'));
        $isAdminRequest = $user !== null
            && ($request->routeIs('admin.*') || $request->is('admin', 'admin/*'));

        return [
            ...parent::share($request),
            'app' => [
                'name' => $siteName,
                'debug' => (bool) config('app.debug'),
            ],
            'navigation' => fn () => SiteNavigation::forUser($user),
            'adminPanel' => fn () => ($isAdminRequest
                ? ['navigation' => AdminNavigation::forUser($user)]
                : null),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'family' => $user->family,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'avatar' => UserAvatarMedia::payload($user),
                    'roles' => $isAdminRequest ? $user->getRoleNames()->values()->all() : [],
                    'permissions' => $isAdminRequest
                        ? $user->getAllPermissions()->pluck('name')->values()->all()
                        : [],
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->hasSession() ? $request->session()->get('success') : null,
                'error' => fn () => $request->hasSession() ? $request->session()->get('error') : null,
            ],
            'cartQty' => fn () => Cart::currentQty(),
            'siteSettings' => fn () => SiteSettingsInertiaPayload::build($settings),
            'siteMenus' => fn () => app(SiteMenuService::class)->groupedByLocation(),
            'siteSocials' => fn () => app(SiteSocialService::class)->forSite(),
            'url' => fn () => $request->url(),
        ];
    }
}

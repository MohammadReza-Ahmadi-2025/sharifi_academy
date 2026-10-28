<?php

namespace App\Http\Middleware;

use App\Modules\Admin\Support\AdminNavigation;
use App\Modules\Cart\Models\Cart;
use App\Modules\Catalog\Services\SiteCourseCategoryNavService;
use App\Modules\Menu\Services\SiteMenuService;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Modules\Settings\Support\SiteSettingsInertiaPayload;
use App\Modules\Social\Services\SiteSocialService;
use App\Modules\Users\Support\UserAvatarMedia;
use App\Support\Notifications\UserNotificationsPresenter;
use App\Support\SiteAccountMenu;
use App\Support\SiteNavigation;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function handle(Request $request, Closure $next): Response
    {
        $this->bridgeSessionFlashToInertia($request);

        return parent::handle($request, $next);
    }

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
                'account' => $user ? SiteAccountMenu::forUser($user) : null,
                'notifications' => fn () => UserNotificationsPresenter::forUser($user),
            ],
            'flash' => [
                'success' => fn () => $request->hasSession() ? $request->session()->get('success') : null,
                'error' => fn () => $request->hasSession() ? $request->session()->get('error') : null,
            ],
            'cartQty' => fn () => Cart::currentQty(),
            'siteSettings' => fn () => SiteSettingsInertiaPayload::build($settings),
            'siteMenus' => fn () => app(SiteMenuService::class)->groupedByLocation(),
            'courseCategoryNav' => fn () => app(SiteCourseCategoryNavService::class)->tree(),
            'siteSocials' => fn () => app(SiteSocialService::class)->forSite(),
            'url' => fn () => $request->url(),
            'otpPolicy' => [
                'expires_seconds' => (int) config('otp.expires_minutes') * 60,
                'resend_cooldown_seconds' => (int) config('otp.resend_cooldown_seconds'),
                'max_verify_attempts' => (int) config('otp.max_attempts'),
            ],
        ];
    }

    private function bridgeSessionFlashToInertia(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $flash = [];

        if ($request->session()->has('success')) {
            $flash['success'] = $request->session()->get('success');
        }

        if ($request->session()->has('error')) {
            $flash['error'] = $request->session()->get('error');
        }

        if ($flash !== []) {
            Inertia::flash($flash);
        }
    }
}

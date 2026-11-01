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
use App\Support\SafeCallable;
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
        $siteName = (string) SafeCallable::run(
            'share.site_name',
            fn () => $settings->get('site_name') ?: config('app.name'),
            config('app.name'),
        );
        $isAdminRequest = $user !== null
            && ($request->routeIs('admin.*') || $request->is('admin', 'admin/*'));

        return [
            ...parent::share($request),
            'app' => [
                'name' => $siteName,
                'debug' => (bool) config('app.debug'),
            ],
            'navigation' => fn () => SafeCallable::run(
                'share.navigation',
                fn () => SiteNavigation::forUser($user),
                ['items' => [], 'cta' => null],
            ),
            'adminPanel' => fn () => SafeCallable::run(
                'share.adminPanel',
                fn () => ($isAdminRequest
                    ? ['navigation' => AdminNavigation::forUser($user)]
                    : null),
                null,
            ),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'family' => $user->family,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'avatar' => SafeCallable::run(
                        'share.avatar',
                        fn () => UserAvatarMedia::payload($user),
                        null,
                    ),
                    'roles' => $isAdminRequest
                        ? SafeCallable::run(
                            'share.roles',
                            fn () => $user->getRoleNames()->values()->all(),
                            [],
                        )
                        : [],
                    'permissions' => $isAdminRequest
                        ? SafeCallable::run(
                            'share.permissions',
                            fn () => $user->getAllPermissions()->pluck('name')->values()->all(),
                            [],
                        )
                        : [],
                ] : null,
                'account' => $user
                    ? SafeCallable::run(
                        'share.account',
                        fn () => SiteAccountMenu::forUser($user),
                        null,
                    )
                    : null,
                'notifications' => fn () => SafeCallable::run(
                    'share.notifications',
                    fn () => UserNotificationsPresenter::forUser($user),
                    null,
                ),
            ],
            'flash' => [
                'success' => fn () => $request->hasSession() ? $request->session()->get('success') : null,
                'error' => fn () => $request->hasSession() ? $request->session()->get('error') : null,
            ],
            'cartQty' => fn () => SafeCallable::run(
                'share.cartQty',
                fn () => Cart::currentQty(),
                0,
            ),
            'siteSettings' => fn () => SafeCallable::run(
                'share.siteSettings',
                fn () => SiteSettingsInertiaPayload::build($settings),
                [],
            ),
            'siteMenus' => fn () => SafeCallable::run(
                'share.siteMenus',
                fn () => app(SiteMenuService::class)->groupedByLocation(),
                [],
            ),
            'courseCategoryNav' => fn () => SafeCallable::run(
                'share.courseCategoryNav',
                fn () => app(SiteCourseCategoryNavService::class)->tree(),
                [],
            ),
            'siteSocials' => fn () => SafeCallable::run(
                'share.siteSocials',
                fn () => app(SiteSocialService::class)->forSite(),
                [],
            ),
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

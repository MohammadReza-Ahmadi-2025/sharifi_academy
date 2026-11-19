<?php

namespace App\Modules\Profile\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Profile\Support\InstructorPublicUuid;
use App\Modules\Profile\Support\UserPublicProfilePresenter;
use App\Modules\Profile\Support\UserPublicProfileSeo;
use App\Modules\Profile\Support\UserPublicSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicUserProfileController extends Controller
{
    public function show(Request $request, string $uuid, ?string $slug = null): Response|RedirectResponse
    {
        $user = $this->resolvePublicUser($uuid);
        $canonicalSlug = UserPublicSlug::forUser($user);

        if ($slug === null || $slug === '' || rawurldecode($slug) !== $canonicalSlug) {
            $url = UserPublicSlug::profileUrl($user, InstructorPublicUuid::routeKey($user));
            $query = $request->getQueryString();
            if ($query !== null && $query !== '') {
                $url .= '?'.$query;
            }

            return redirect()->to($url, 301);
        }

        $user->load([
            'profile.provinceCity.parent',
            'skills',
            'resumes' => fn ($query) => $query
                ->with('media')
                ->orderBy('display_order')
                ->orderByDesc('start_date'),
        ]);

        $profile = UserPublicProfilePresenter::forPage($user);
        $appName = (string) config('app.name');
        $seo = UserPublicProfileSeo::forShow($profile, $appName);

        return Inertia::render('Public/Users/Show', [
            'profile' => $profile,
            'seo' => $seo,
            'structuredData' => [
                'breadcrumb' => UserPublicProfileSeo::breadcrumbJsonLd($profile),
                'person' => UserPublicProfileSeo::personJsonLd($profile, $appName),
                'profilePage' => UserPublicProfileSeo::profilePageJsonLd($seo, $appName),
            ],
        ]);
    }

    private function resolvePublicUser(string $uuid): User
    {
        $user = InstructorPublicUuid::findUserByPublicUuid($uuid);
        abort_unless($user instanceof User && self::isPubliclyViewable($user), 404);

        return $user;
    }

    public static function isPubliclyViewable(User $user): bool
    {
        if ((int) $user->status !== 1) {
            return false;
        }

        $user->loadMissing('profile');

        // Default true when profile row missing / null column.
        return (bool) ($user->profile?->is_public ?? true);
    }
}

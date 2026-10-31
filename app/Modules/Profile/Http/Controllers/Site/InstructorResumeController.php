<?php

namespace App\Modules\Profile\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Profile\Support\InstructorPublicResumePresenter;
use App\Modules\Profile\Support\InstructorSeo;
use App\Modules\Profile\Support\ResumeModeration;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class InstructorResumeController extends Controller
{
    public function showPage(User $user): Response
    {
        abort_unless(self::isPublicInstructor($user), 404);

        $profile = $this->loadPublicProfile($user);
        $appName = (string) config('app.name');
        $seo = InstructorSeo::forShow($profile, $appName);

        return Inertia::render('Public/InstructorResume/Show', [
            'profile' => $profile,
            'seo' => $seo,
            'structuredData' => [
                'breadcrumb' => InstructorSeo::breadcrumbJsonLd($profile),
                'person' => InstructorSeo::personJsonLd($profile, $appName),
                'profilePage' => InstructorSeo::profilePageJsonLd($seo, $appName),
            ],
        ]);
    }

    public function show(User $user): JsonResponse
    {
        abort_unless(self::isPublicInstructor($user), 404);

        return response()->json($this->loadPublicProfile($user));
    }

    /**
     * @return array<string, mixed>
     */
    private function loadPublicProfile(User $user): array
    {
        $user->load([
            'profile',
            'skills',
            'resumes' => fn ($query) => $query
                ->with('media')
                ->orderBy('display_order')
                ->orderByDesc('start_date'),
        ]);

        return InstructorPublicResumePresenter::forUser($user);
    }

    public static function isPublicInstructor(User $user): bool
    {
        return (int) $user->status === 1
            && $user->hasRole('instructor')
            && ResumeModeration::isPubliclyVisible($user);
    }
}

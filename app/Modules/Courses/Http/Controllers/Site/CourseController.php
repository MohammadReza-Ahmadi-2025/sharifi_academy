<?php

namespace App\Modules\Courses\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\CoursePublicPresenter;
use App\Modules\Courses\Support\CourseSeo;
use App\Support\Http\CanonicalIdSlugRedirect;
use App\Support\Search\SiteSearchUrls;
use App\Support\SeoSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function show(Request $request, Course $course, string $slug): Response|RedirectResponse
    {
        CoursePublicPresenter::ensurePublished($course);

        $redirect = CanonicalIdSlugRedirect::awayIfSlugMismatched(
            $request,
            $course,
            'courses.show',
            'course',
            $slug,
        );
        if ($redirect !== null) {
            return $redirect;
        }

        $course->recordVisit();

        $payload = CoursePublicPresenter::forPage($course, $request->user());
        $appName = (string) config('app.name');

        return Inertia::render('Public/CourseShow', [
            'course' => $payload,
            'structuredData' => [
                'breadcrumb' => CourseSeo::breadcrumbJsonLd($course),
                'course' => CourseSeo::courseJsonLd(
                    $course,
                    $appName,
                    $payload['price_from'],
                    $payload['instructors'],
                ),
                'webPage' => CourseSeo::webPageJsonLd($course, $appName),
            ],
        ]);
    }

    public function redirectLegacySlug(string $slug): RedirectResponse
    {
        $course = SeoSlug::resolveModel(Course::query(), $slug, 'slug');
        abort_if($course === null, 404);

        CoursePublicPresenter::ensurePublished($course);

        return redirect()->to(url(SiteSearchUrls::course($course)), 301);
    }
}

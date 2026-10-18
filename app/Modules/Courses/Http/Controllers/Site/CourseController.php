<?php

namespace App\Modules\Courses\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\CoursePublicPresenter;
use App\Modules\Courses\Support\CourseSeo;
use App\Support\Http\CanonicalSeoSlugRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function show(Request $request, Course $course): Response|RedirectResponse
    {
        CoursePublicPresenter::ensurePublished($course);

        $redirect = CanonicalSeoSlugRedirect::awayIfMismatched(
            $request,
            $course,
            'courses.show',
            'course',
            2,
        );
        if ($redirect !== null) {
            return $redirect;
        }

        $canonicalSlug = $course->publicSlug();
        $requestedSlug = rawurldecode((string) $request->segment(2));
        if ($requestedSlug !== '' && $requestedSlug !== $canonicalSlug) {
            $url = route('courses.show', ['course' => $canonicalSlug]);
            $query = $request->getQueryString();
            if ($query !== null && $query !== '') {
                $url .= '?'.$query;
            }

            return redirect()->to($url, 301);
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
}

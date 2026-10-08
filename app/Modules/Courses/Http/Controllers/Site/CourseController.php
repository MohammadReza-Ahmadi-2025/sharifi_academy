<?php

namespace App\Modules\Courses\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\CoursePublicPresenter;
use App\Modules\Courses\Support\CourseSeo;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function show(Course $course): Response
    {
        CoursePublicPresenter::ensurePublished($course);
        $course->recordVisit();

        $payload = CoursePublicPresenter::forPage($course);
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

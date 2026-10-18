<?php

namespace App\Modules\Courses\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Http\Requests\Site\StoreSiteEnrollmentRequest;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Services\SiteEnrollmentRequestService;
use Illuminate\Http\RedirectResponse;

class CourseEnrollmentController extends Controller
{
    public function store(
        StoreSiteEnrollmentRequest $request,
        Course $course,
        SiteEnrollmentRequestService $enrollmentRequests,
    ): RedirectResponse {
        abort_unless($course->status === CourseStatus::Published && ! $course->trashed(), 404);

        $user = $request->user();
        abort_unless($user !== null, 403);

        $courseClass = $request->courseClass();

        $this->authorize('requestEnrollment', $courseClass);

        $enrollmentRequests->request($user, $courseClass);

        return back()->with('success', 'درخواست ثبت‌نام شما ثبت شد. پس از بررسی آموزش، نتیجه از طریق حساب کاربری اطلاع‌رسانی می‌شود.');
    }
}

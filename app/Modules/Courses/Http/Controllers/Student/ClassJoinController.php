<?php

namespace App\Modules\Courses\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Services\SyncAdobeConnectEnrollmentService;
use App\Modules\AdobeConnect\Support\AdobeConnectJoinUrl;
use App\Modules\AdobeConnect\Support\AdobeConnectUrlBuilder;
use App\Modules\AdobeConnect\Support\AdobeConnectUserCredentials;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClassJoinController extends Controller
{
    public function __invoke(
        Request $request,
        CourseClass $courseClass,
        SyncAdobeConnectEnrollmentService $adobeEnrollmentSync,
        AdobeConnectUrlBuilder $adobeUrls,
    ): RedirectResponse {
        $this->authorize('joinClass', $courseClass);

        $user = $request->user();
        abort_unless($user !== null, 403);

        $courseClass->loadMissing(['classLevelAdobeRooms']);

        abort_if($courseClass->usesVideoLink(), 404, 'برای ورود به کلاس ویدیویی یک جلسه با لینک بسازید.');

        $enrollment = Enrollment::query()
            ->confirmed()
            ->where('user_id', $user->id)
            ->where('course_class_id', $courseClass->id)
            ->first();

        if ($enrollment !== null) {
            $adobeEnrollmentSync->sync($enrollment);
        }

        $urlPath = AdobeConnectJoinUrl::urlPathForClass($courseClass);
        abort_if($urlPath === null, 404, 'اتاق Adobe Connect برای این کلاس تنظیم نشده است.');

        $credentials = AdobeConnectUserCredentials::forUser($user);
        abort_if($credentials === null, 422, 'شماره موبایل حساب شما برای ورود به Adobe Connect معتبر نیست.');

        return redirect()->away($adobeUrls->meetingJoinUrlWithCredentials(
            $urlPath,
            $credentials->login,
            $credentials->password,
        ));
    }
}

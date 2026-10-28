<?php

namespace App\Modules\Courses\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Services\SyncAdobeConnectEnrollmentService;
use App\Modules\AdobeConnect\Support\AdobeConnectJoinUrl;
use App\Modules\AdobeConnect\Support\AdobeConnectUrlBuilder;
use App\Modules\AdobeConnect\Support\AdobeConnectUserCredentials;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Support\ClassSessionJoinUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SessionJoinController extends Controller
{
    public function __invoke(
        Request $request,
        CourseClass $courseClass,
        ClassSession $classSession,
        SyncAdobeConnectEnrollmentService $adobeEnrollmentSync,
        AdobeConnectUrlBuilder $adobeUrls,
    ): RedirectResponse {
        abort_unless($classSession->course_class_id === $courseClass->id, 404);

        $this->authorize('joinSession', $classSession);

        $user = $request->user();
        abort_unless($user !== null, 403);

        $classSession->loadMissing(['adobeRooms']);
        $courseClass->loadMissing(['classLevelAdobeRooms']);

        if ($courseClass->usesVideoLink()) {
            $target = ClassSessionJoinUrl::externalTarget($classSession, $courseClass);
            abort_if($target === null || $target === '', 404, 'لینک کلاس آنلاین برای این جلسه تنظیم نشده است.');

            return redirect()->away($target);
        }

        $enrollment = Enrollment::query()
            ->confirmed()
            ->where('user_id', $user->id)
            ->where('course_class_id', $courseClass->id)
            ->first();

        if ($enrollment !== null) {
            $adobeEnrollmentSync->sync($enrollment);
        }

        $urlPath = AdobeConnectJoinUrl::urlPathForSession($classSession, $courseClass);
        abort_if($urlPath === null, 404, 'اتاق Adobe Connect برای این کلاس تنظیم نشده است.');

        $credentials = AdobeConnectUserCredentials::forUser($user);
        abort_if($credentials === null, 422, 'شماره موبایل حساب شما برای ورود به Adobe Connect معتبر نیست.');

        $target = $adobeUrls->meetingJoinUrlWithCredentials(
            $urlPath,
            $credentials->login,
            $credentials->password,
        );

        return redirect()->away($target);
    }
}

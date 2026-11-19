<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Services\SyncAdobeConnectClassInstructorsService;
use App\Modules\AdobeConnect\Support\AdobeConnectJoinUrl;
use App\Modules\AdobeConnect\Support\AdobeConnectUrlBuilder;
use App\Modules\AdobeConnect\Support\AdobeConnectUserCredentials;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClassJoinController extends Controller
{
    public function __invoke(
        Request $request,
        CourseClass $courseClass,
        SyncAdobeConnectClassInstructorsService $instructorAdobeSync,
        AdobeConnectUrlBuilder $adobeUrls,
    ): RedirectResponse {
        $this->authorize('joinClass', $courseClass);

        $user = $request->user();
        abort_unless($user !== null, 403);

        $courseClass->loadMissing(['classLevelAdobeRooms', 'course']);

        abort_if($courseClass->usesVideoLink(), 404, 'برای ورود به کلاس ویدیویی یک جلسه با لینک بسازید.');

        // Admin/super-admin enter as host (same Adobe permission as class instructors).
        $instructorAdobeSync->syncUser($courseClass, $user);

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

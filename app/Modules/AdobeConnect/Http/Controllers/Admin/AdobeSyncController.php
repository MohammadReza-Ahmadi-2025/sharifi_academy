<?php

namespace App\Modules\AdobeConnect\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Http\Requests\Admin\SyncAdobeRoomRequest;
use App\Modules\AdobeConnect\Http\Requests\Admin\SyncAdobeSessionRecordingsRequest;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Services\SyncAdobeRoomFromApiService;
use App\Modules\AdobeConnect\Services\SyncAdobeSessionRecordingsService;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\EnsuresNestedCourseAccess;
use Illuminate\Http\RedirectResponse;
use Throwable;

class AdobeSyncController extends Controller
{
    use EnsuresNestedCourseAccess;

    public function syncRoom(
        SyncAdobeRoomRequest $request,
        Course $course,
        CourseClass $courseClass,
        AdobeRoom $adobeRoom,
        SyncAdobeRoomFromApiService $sync,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);

        if ($adobeRoom->course_class_id !== $courseClass->id) {
            abort(404);
        }

        $this->authorize('update', $adobeRoom);
        try {
            $sync->sync($adobeRoom);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'سینک اتاق Adobe Connect ناموفق بود؛ تنظیمات اتصال و sco-id را بررسی کنید.');
        }

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.edit', [$course, $courseClass])
            ->with('success', 'اتاق Adobe از سرور به‌روزرسانی شد.');
    }

    public function syncSessionRoom(
        SyncAdobeRoomRequest $request,
        Course $course,
        CourseClass $courseClass,
        ClassSession $classSession,
        AdobeRoom $adobeRoom,
        SyncAdobeRoomFromApiService $sync,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);

        if ($adobeRoom->course_class_id !== $courseClass->id || $adobeRoom->class_session_id !== $session->id) {
            abort(404);
        }

        $this->authorize('update', $adobeRoom);
        try {
            $sync->sync($adobeRoom);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'سینک اتاق Adobe Connect ناموفق بود؛ تنظیمات اتصال و sco-id را بررسی کنید.');
        }

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', 'اتاق Adobe از سرور به‌روزرسانی شد.');
    }

    public function syncSessionRecordings(
        SyncAdobeSessionRecordingsRequest $request,
        Course $course,
        CourseClass $courseClass,
        ClassSession $classSession,
        SyncAdobeSessionRecordingsService $sync,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);
        $this->authorize('syncAdobeRecordings', $session);

        try {
            $count = count($sync->syncForSession($session, $courseClass));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'دریافت ضبط‌های Adobe Connect ناموفق بود؛ اتصال و اتاق فعال جلسه را بررسی کنید.');
        }

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', "{$count} ضبط از Adobe Connect وارد شد.");
    }
}

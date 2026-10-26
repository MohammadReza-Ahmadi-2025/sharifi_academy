<?php

namespace App\Modules\AdobeConnect\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Http\Requests\Admin\DestroyAdobeRoomRequest;
use App\Modules\AdobeConnect\Http\Requests\Admin\StoreAdobeRoomRequest;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Services\AdobeRoomPayloadFactory;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\EnsuresNestedCourseAccess;
use Illuminate\Http\RedirectResponse;

class AdobeRoomController extends Controller
{
    use EnsuresNestedCourseAccess;

    public function storeForClass(
        StoreAdobeRoomRequest $request,
        Course $course,
        CourseClass $courseClass,
        AdobeRoomPayloadFactory $payloadFactory,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $this->authorize('createAdobeRoom', $courseClass);

        AdobeRoom::query()->create([
            ...$payloadFactory->resolveForCreate($request->validated()),
            'course_class_id' => $courseClass->id,
            'class_session_id' => null,
        ]);

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.edit', [$course, $courseClass])
            ->with('success', 'اتاق ادوبی کلاس ثبت شد.');
    }

    public function storeForSession(
        StoreAdobeRoomRequest $request,
        Course $course,
        CourseClass $courseClass,
        ClassSession $classSession,
        AdobeRoomPayloadFactory $payloadFactory,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);
        $this->authorize('createAdobeRoom', $session);

        AdobeRoom::query()->create([
            ...$payloadFactory->resolveForCreate($request->validated()),
            'course_class_id' => $courseClass->id,
            'class_session_id' => $session->id,
        ]);

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', 'اتاق ادوبی جلسه ثبت شد.');
    }

    public function destroyForClass(
        DestroyAdobeRoomRequest $request,
        Course $course,
        CourseClass $courseClass,
        AdobeRoom $adobeRoom,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);

        if ($adobeRoom->course_class_id !== $courseClass->id || $adobeRoom->class_session_id !== null) {
            abort(404);
        }

        $this->authorize('delete', $adobeRoom);
        $adobeRoom->delete();

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.edit', [$course, $courseClass])
            ->with('success', 'اتاق حذف شد.');
    }

    public function destroyForSession(
        DestroyAdobeRoomRequest $request,
        Course $course,
        CourseClass $courseClass,
        ClassSession $classSession,
        AdobeRoom $adobeRoom,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);

        if ($adobeRoom->course_class_id !== $courseClass->id || $adobeRoom->class_session_id !== $session->id) {
            abort(404);
        }

        $this->authorize('delete', $adobeRoom);
        $adobeRoom->delete();

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', 'اتاق جلسه حذف شد.');
    }
}

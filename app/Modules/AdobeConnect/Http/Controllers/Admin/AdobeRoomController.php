<?php

namespace App\Modules\AdobeConnect\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Http\Requests\Admin\StoreAdobeRoomRequest;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\EnsuresNestedCourseAccess;
use Illuminate\Http\RedirectResponse;

class AdobeRoomController extends Controller
{
    use EnsuresNestedCourseAccess;

    public function storeForClass(StoreAdobeRoomRequest $request, Course $course, CourseClass $courseClass): RedirectResponse
    {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);

        AdobeRoom::query()->create([
            ...$request->validated(),
            'course_class_id' => $courseClass->id,
            'class_session_id' => null,
        ]);

        return redirect()
            ->route('admin.courses.classes.edit', [$course, $courseClass])
            ->with('success', 'اتاق ادوبی کلاس ثبت شد.');
    }

    public function storeForSession(
        StoreAdobeRoomRequest $request,
        Course $course,
        CourseClass $courseClass,
        ClassSession $classSession,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);

        AdobeRoom::query()->create([
            ...$request->validated(),
            'course_class_id' => $courseClass->id,
            'class_session_id' => $session->id,
        ]);

        return redirect()
            ->route('admin.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', 'اتاق ادوبی جلسه ثبت شد.');
    }

    public function destroyForClass(Course $course, CourseClass $courseClass, AdobeRoom $adobeRoom): RedirectResponse
    {
        abort_unless(auth()->user()?->can('adobe-rooms-delete'), 403);
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);

        if ($adobeRoom->course_class_id !== $courseClass->id) {
            abort(404);
        }

        $adobeRoom->delete();

        return redirect()
            ->route('admin.courses.classes.edit', [$course, $courseClass])
            ->with('success', 'اتاق حذف شد.');
    }
}

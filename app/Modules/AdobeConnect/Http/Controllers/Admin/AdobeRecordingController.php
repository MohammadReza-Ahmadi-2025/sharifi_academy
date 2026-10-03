<?php

namespace App\Modules\AdobeConnect\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Http\Requests\Admin\StoreAdobeRecordingRequest;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\EnsuresNestedCourseAccess;
use Illuminate\Http\RedirectResponse;

class AdobeRecordingController extends Controller
{
    use EnsuresNestedCourseAccess;

    public function store(
        StoreAdobeRecordingRequest $request,
        Course $course,
        CourseClass $courseClass,
        ClassSession $classSession,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);

        AdobeRecording::query()->create([
            ...$request->validated(),
            'class_session_id' => $session->id,
        ]);

        return redirect()
            ->route('admin.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', 'ضبط ادوبی ثبت شد.');
    }

    public function destroy(
        Course $course,
        CourseClass $courseClass,
        ClassSession $classSession,
        AdobeRecording $adobeRecording,
    ): RedirectResponse {
        abort_unless(auth()->user()?->can('adobe-recordings-delete'), 403);
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);

        if ($adobeRecording->class_session_id !== $session->id) {
            abort(404);
        }

        $adobeRecording->delete();

        return redirect()
            ->route('admin.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', 'ضبط حذف شد.');
    }
}

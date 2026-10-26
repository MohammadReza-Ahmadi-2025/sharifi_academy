<?php

namespace App\Modules\AdobeConnect\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Http\Requests\Admin\DestroyAdobeRecordingRequest;
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
        $this->authorize('syncAdobeRecordings', $session);

        AdobeRecording::query()->create([
            ...$request->validated(),
            'class_session_id' => $session->id,
        ]);

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', 'ضبط ادوبی ثبت شد.');
    }

    public function destroy(
        DestroyAdobeRecordingRequest $request,
        Course $course,
        CourseClass $courseClass,
        ClassSession $classSession,
        AdobeRecording $adobeRecording,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);

        if ($adobeRecording->class_session_id !== $session->id) {
            abort(404);
        }

        $this->authorize('delete', $adobeRecording);
        $adobeRecording->delete();

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', 'ضبط حذف شد.');
    }
}

<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Models\AdobeRecording;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Support\AdminAdobeEnumOptions;
use App\Modules\Courses\Enums\ClassSessionStatus;
use App\Modules\Courses\Http\Requests\Admin\StoreClassSessionRequest;
use App\Modules\Courses\Http\Requests\Admin\UpdateClassSessionRequest;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\AdminEnumOptions;
use App\Modules\Courses\Support\ClassSessionPresenter;
use App\Modules\Courses\Support\CourseClassPresenter;
use App\Modules\Courses\Support\CoursePresenter;
use App\Modules\Courses\Support\EnsuresNestedCourseAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClassSessionController extends Controller
{
    use EnsuresNestedCourseAccess;

    public function index(Request $request, Course $course, CourseClass $courseClass): Response
    {
        abort_unless($request->user()?->can('class-sessions-index'), 403);
        $this->authorizeManagedCourse($request, $course);
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);

        $sessions = $courseClass->classSessions()
            ->with(['adobeRooms'])
            ->get()
            ->map(fn (ClassSession $session) => ClassSessionPresenter::forList($session, $courseClass));

        $actor = $request->user();

        return Inertia::render('Admin/Courses/Classes/Sessions/Index', [
            'course' => CoursePresenter::forShow($course),
            'courseClass' => CourseClassPresenter::forList($courseClass),
            'sessions' => $sessions,
            'routePrefix' => '/'.$this->nestedCourseRoutePrefix($request),
            'abilities' => [
                'create' => $actor?->can('class-sessions-create') ?? false,
                'update' => $actor?->can('class-sessions-update') ?? false,
                'delete' => $actor?->can('class-sessions-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request, Course $course, CourseClass $courseClass): Response
    {
        abort_unless($request->user()?->can('class-sessions-create'), 403);
        $this->authorizeManagedCourse($request, $course, 'update');
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);

        return Inertia::render('Admin/Courses/Classes/Sessions/Create', [
            'course' => CoursePresenter::forShow($course),
            'courseClass' => CourseClassPresenter::forList($courseClass),
            'statusOptions' => AdminEnumOptions::fromEnum(ClassSessionStatus::class),
            'routePrefix' => '/'.$this->nestedCourseRoutePrefix($request),
        ]);
    }

    public function store(StoreClassSessionRequest $request, Course $course, CourseClass $courseClass): RedirectResponse
    {
        $this->authorizeManagedCourse($request, $course, 'update');
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $data = $request->validated();

        $session = $courseClass->classSessions()->create([
            ...$data,
            'timezone' => $data['timezone'] ?? 'Asia/Tehran',
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', 'جلسه ایجاد شد.');
    }

    public function edit(Request $request, Course $course, CourseClass $courseClass, ClassSession $classSession): Response
    {
        abort_unless($request->user()?->can('class-sessions-update'), 403);
        $this->authorizeManagedCourse($request, $course, 'update');
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);

        $actor = $request->user();

        $sessionRooms = $actor?->can('adobe-rooms-index')
            ? $session->adobeRooms()->latest()->get()->map(fn (AdobeRoom $room) => [
                'id' => $room->id,
                'sco_id' => $room->sco_id,
                'url_path' => $room->url_path,
                'meeting_url' => $room->meeting_url,
                'status_label' => $room->status_label,
            ])
            : collect();

        $recordings = $actor?->can('adobe-recordings-index')
            ? $session->adobeRecordings()->latest()->get()->map(fn (AdobeRecording $recording) => [
                'id' => $recording->id,
                'sco_id' => $recording->sco_id,
                'name' => $recording->name,
                'playback_url' => $recording->playback_url,
                'download_url' => $recording->download_url,
                'is_public' => $recording->is_public,
                'duration_seconds' => $recording->duration_seconds,
            ])
            : collect();

        return Inertia::render('Admin/Courses/Classes/Sessions/Edit', [
            'course' => CoursePresenter::forShow($course),
            'courseClass' => CourseClassPresenter::forList($courseClass),
            'session' => ClassSessionPresenter::forForm($session),
            'statusOptions' => AdminEnumOptions::fromEnum(ClassSessionStatus::class),
            'adobeRooms' => $sessionRooms,
            'adobeRecordings' => $recordings,
            'adobeAccessOptions' => AdminAdobeEnumOptions::accessTypes(),
            'adobeStatusOptions' => AdminAdobeEnumOptions::roomStatuses(),
            'routePrefix' => '/'.$this->nestedCourseRoutePrefix($request),
            'abilities' => [
                'adobeIndex' => $actor?->can('adobe-rooms-index') ?? false,
                'adobeCreate' => $actor?->can('adobe-rooms-create') ?? false,
                'adobeDelete' => $actor?->can('adobe-rooms-delete') ?? false,
                'recordingIndex' => $actor?->can('adobe-recordings-index') ?? false,
                'recordingCreate' => $actor?->can('adobe-recordings-create') ?? false,
                'recordingDelete' => $actor?->can('adobe-recordings-delete') ?? false,
                'adobeSync' => $actor?->can('adobe-rooms-update') ?? false,
                'recordingSync' => $actor?->can('adobe-recordings-create') ?? false,
            ],
        ]);
    }

    public function update(UpdateClassSessionRequest $request, Course $course, CourseClass $courseClass, ClassSession $classSession): RedirectResponse
    {
        $this->authorizeManagedCourse($request, $course, 'update');
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);

        $session->update($request->validated());

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.sessions.edit', [$course, $courseClass, $session])
            ->with('success', 'جلسه به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Course $course, CourseClass $courseClass, ClassSession $classSession): RedirectResponse
    {
        abort_unless($request->user()?->can('class-sessions-delete'), 403);
        $this->authorizeManagedCourse($request, $course, 'update');
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $session = $this->classSessionOrFail($course, $courseClass, $classSession->id);
        $session->delete();

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.sessions.index', [$course, $courseClass])
            ->with('success', 'جلسه حذف شد.');
    }
}

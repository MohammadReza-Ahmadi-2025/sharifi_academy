<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Models\AdobeRoom;
use App\Modules\AdobeConnect\Services\ProvisionCourseClassAdobeMeetingService;
use App\Modules\AdobeConnect\Services\SyncAdobeConnectClassInstructorsService;
use App\Modules\AdobeConnect\Support\AdminAdobeEnumOptions;
use App\Modules\Courses\Enums\ClassInstructorRole;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Http\Requests\Admin\StoreCourseClassRequest;
use App\Modules\Courses\Http\Requests\Admin\UpdateCourseClassRequest;
use App\Modules\Courses\Models\ClassInstructor;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Support\AdminEnumOptions;
use App\Modules\Courses\Support\CourseClassPresenter;
use App\Modules\Courses\Support\CoursePresenter;
use App\Modules\Courses\Support\EnrollableUserOptions;
use App\Modules\Courses\Support\InstructorUserOptions;
use App\Modules\Courses\Support\EnsuresNestedCourseAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseClassController extends Controller
{
    use EnsuresNestedCourseAccess;

    public function index(Request $request, Course $course): Response
    {
        abort_unless($request->user()?->can('course-classes-index'), 403);
        $this->authorizeManagedCourse($request, $course);

        $classes = $course->courseClasses()
            ->withCount(['classSessions', 'enrollments'])
            ->orderBy('name')
            ->get()
            ->map(fn (CourseClass $class) => CourseClassPresenter::forList($class));

        $actor = $request->user();

        return Inertia::render('Admin/Courses/Classes/Index', [
            'course' => CoursePresenter::forShow($course),
            'classes' => $classes,
            'routePrefix' => '/'.$this->nestedCourseRoutePrefix($request),
            'abilities' => [
                'create' => $actor?->can('course-classes-create') ?? false,
                'update' => $actor?->can('course-classes-update') ?? false,
                'delete' => $actor?->can('course-classes-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request, Course $course): Response
    {
        abort_unless($request->user()?->can('course-classes-create'), 403);
        $this->authorizeManagedCourse($request, $course, 'update');

        return Inertia::render('Admin/Courses/Classes/Create', [
            'course' => CoursePresenter::forShow($course),
            'routePrefix' => '/'.$this->nestedCourseRoutePrefix($request),
            ...$this->formOptions(),
        ]);
    }

    public function store(
        StoreCourseClassRequest $request,
        Course $course,
        ProvisionCourseClassAdobeMeetingService $adobeMeetings,
    ): RedirectResponse {
        $this->authorizeManagedCourse($request, $course, 'update');

        $data = $request->validated();

        $courseClass = $course->courseClasses()->create([
            ...$this->mappedAttributes($data),
            'created_by' => $request->user()?->id,
        ]);

        $this->syncInstructors($courseClass, $data['instructors'] ?? []);

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.edit', [$course, $courseClass])
            ->with('success', $this->storeSuccessMessage($courseClass, $adobeMeetings));
    }

    public function edit(Request $request, Course $course, CourseClass $courseClass): Response
    {
        abort_unless($request->user()?->can('course-classes-update'), 403);
        $this->authorizeManagedCourse($request, $course, 'update');
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);

        $courseClass->load(['classInstructors.user']);

        $actor = $request->user();

        $enrollments = $actor?->can('enrollments-index')
            ? $courseClass->enrollments()
                ->with('user')
                ->latest()
                ->get()
                ->map(fn (Enrollment $enrollment) => [
                    'id' => $enrollment->id,
                    'user_id' => $enrollment->user_id,
                    'user_label' => trim(($enrollment->user?->name ?? '').' '.($enrollment->user?->family ?? '')),
                    'user_mobile' => $enrollment->user?->mobile,
                    'adobe_linked' => filled($enrollment->user?->adobe_connect_principal_id),
                    'status' => $enrollment->status?->value,
                    'status_label' => $enrollment->status?->label(),
                    'price_amount' => $enrollment->price_amount,
                ])
            : collect();

        $classAdobeRooms = $actor?->can('adobe-rooms-index')
            ? $courseClass->classLevelAdobeRooms()
                ->latest()
                ->get()
                ->map(fn (AdobeRoom $room) => [
                    'id' => $room->id,
                    'sco_id' => $room->sco_id,
                    'url_path' => $room->url_path,
                    'meeting_url' => $room->meeting_url,
                    'status' => $room->status?->value,
                    'status_label' => $room->status?->label(),
                ])
            : collect();

        return Inertia::render('Admin/Courses/Classes/Edit', [
            'course' => CoursePresenter::forShow($course),
            'courseClass' => CourseClassPresenter::forForm($courseClass),
            'enrollments' => $enrollments,
            'adobeRooms' => $classAdobeRooms,
            ...$this->formOptions(),
            'enrollmentStatusOptions' => AdminEnumOptions::fromEnum(EnrollmentStatus::class),
            'userOptions' => $this->userOptions(),
            'enrollmentUserOptions' => EnrollableUserOptions::forSelect(),
            'adobeAccessOptions' => AdminAdobeEnumOptions::accessTypes(),
            'adobeStatusOptions' => AdminAdobeEnumOptions::roomStatuses(),
            'routePrefix' => '/'.$this->nestedCourseRoutePrefix($request),
            'abilities' => [
                'sessions' => $actor?->can('class-sessions-index') ?? false,
                'enrollmentsIndex' => $actor?->can('enrollments-index') ?? false,
                'enrollmentsCreate' => $actor?->can('enrollments-create') ?? false,
                'enrollmentsUpdate' => $actor?->can('enrollments-update') ?? false,
                'enrollmentsDelete' => $actor?->can('enrollments-delete') ?? false,
                'adobeIndex' => $actor?->can('adobe-rooms-index') ?? false,
                'adobeCreate' => $actor?->can('adobe-rooms-create') ?? false,
                'adobeUpdate' => $actor?->can('adobe-rooms-update') ?? false,
                'adobeDelete' => $actor?->can('adobe-rooms-delete') ?? false,
            ],
        ]);
    }

    public function update(
        UpdateCourseClassRequest $request,
        Course $course,
        CourseClass $courseClass,
        ProvisionCourseClassAdobeMeetingService $adobeMeetings,
    ): RedirectResponse {
        $this->authorizeManagedCourse($request, $course, 'update');
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $data = $request->validated();

        $courseClass->update($this->mappedAttributes($data));
        $this->syncInstructors($courseClass, $data['instructors'] ?? []);

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.edit', [$course, $courseClass])
            ->with('success', $this->updateSuccessMessage($courseClass, $adobeMeetings));
    }

    public function destroy(Request $request, Course $course, CourseClass $courseClass): RedirectResponse
    {
        abort_unless($request->user()?->can('course-classes-delete'), 403);
        $this->authorizeManagedCourse($request, $course, 'update');
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $courseClass->delete();

        return redirect()
            ->route($this->nestedCourseRoutePrefix($request).'.courses.classes.index', $course)
            ->with('success', 'کلاس حذف شد.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mappedAttributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'code' => $data['code'],
            'status' => $data['status'],
            'delivery_type' => $data['delivery_type'] ?? CourseClassDeliveryType::Online->value,
            'online_platform' => $data['online_platform'] ?? CourseClassOnlinePlatform::AdobeConnect->value,
            'capacity' => $data['capacity'] ?? null,
            'price' => $data['price'] ?? 0,
            'registration_starts_at' => $data['registration_starts_at'] ?? null,
            'registration_ends_at' => $data['registration_ends_at'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'meeting_weekdays' => $data['meeting_weekdays'] ?? [],
            'meeting_starts_time' => $data['meeting_starts_time'] ?? null,
            'meeting_ends_time' => $data['meeting_ends_time'] ?? null,
            'location' => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'is_public' => $data['is_public'] ?? false,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncInstructors(CourseClass $courseClass, array $rows): void
    {
        $courseClass->classInstructors()->delete();

        foreach ($rows as $row) {
            ClassInstructor::query()->create([
                'course_class_id' => $courseClass->id,
                'user_id' => $row['user_id'],
                'role' => $row['role'],
                'is_primary' => $row['is_primary'] ?? false,
                'share_percentage' => $row['share_percentage'] ?? null,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'statusOptions' => AdminEnumOptions::fromEnum(CourseClassStatus::class),
            'deliveryTypeOptions' => AdminEnumOptions::fromEnum(CourseClassDeliveryType::class),
            'onlinePlatformOptions' => AdminEnumOptions::fromEnum(CourseClassOnlinePlatform::class),
            'instructorRoleOptions' => AdminEnumOptions::fromEnum(ClassInstructorRole::class),
            'userOptions' => $this->userOptions(),
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    private function userOptions(): array
    {
        return InstructorUserOptions::forSelect();
    }

    private function storeSuccessMessage(
        CourseClass $courseClass,
        ProvisionCourseClassAdobeMeetingService $adobeMeetings,
    ): string {
        $message = 'کلاس ایجاد شد.';

        return $this->appendAdobeProvisionMessage($courseClass, $adobeMeetings, $message);
    }

    private function updateSuccessMessage(
        CourseClass $courseClass,
        ProvisionCourseClassAdobeMeetingService $adobeMeetings,
    ): string {
        $message = 'کلاس به‌روزرسانی شد.';

        return $this->appendAdobeProvisionMessage($courseClass, $adobeMeetings, $message);
    }

    private function appendAdobeProvisionMessage(
        CourseClass $courseClass,
        ProvisionCourseClassAdobeMeetingService $adobeMeetings,
        string $message,
    ): string {
        $fresh = $courseClass->fresh() ?? $courseClass;

        try {
            $room = $adobeMeetings->provisionIfNeeded($fresh);
            if ($room !== null) {
                $message .= ' اتاق Adobe Connect به‌صورت خودکار ساخته و وصل شد.';
            }
        } catch (\Throwable $exception) {
            report($exception);

            return $message.' ساخت خودکار اتاق Adobe انجام نشد؛ از بخش «اتاق ادوبی» دستی تنظیم کنید.';
        }

        try {
            $instructorStats = app(SyncAdobeConnectClassInstructorsService::class)->sync($fresh->fresh() ?? $fresh);
            $instructorTouched = $instructorStats['linked'] + $instructorStats['created'];
            if ($instructorTouched > 0) {
                $message .= " {$instructorTouched} استاد به عنوان host اتاق Adobe وصل شد.";
            }
            if ($instructorStats['failed'] > 0) {
                $message .= " {$instructorStats['failed']} استاد در Adobe ساخته/وصل نشد.";
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $message;
    }
}

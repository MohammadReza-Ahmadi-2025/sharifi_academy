<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Http\Requests\Admin\StoreCourseEnrollmentPageRequest;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Services\AdminEnrollmentService;
use App\Modules\Courses\Support\AdminEnumOptions;
use App\Modules\Courses\Support\EnrollableUserOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseEnrollmentAdminController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('enrollments-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'pending');
        if (! in_array($status, ['pending', 'confirmed', 'cancelled', 'all'], true)) {
            $status = 'pending';
        }
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Enrollment::query()
            ->with(['user', 'courseClass.course'])
            ->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->whereHas('user', function ($userQuery) use ($search): void {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('family', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    })
                    ->orWhereHas('courseClass.course', fn ($courseQuery) => $courseQuery->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('courseClass', fn ($classQuery) => $classQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $enrollments = $query->paginate($perPage)->withQueryString()->through(
            fn (Enrollment $enrollment) => [
                'id' => $enrollment->id,
                'user_label' => trim(($enrollment->user?->name ?? '').' '.($enrollment->user?->family ?? '')),
                'user_mobile' => $enrollment->user?->mobile,
                'course_title' => $enrollment->courseClass?->course?->title,
                'course_id' => $enrollment->courseClass?->course_id,
                'class_name' => $enrollment->courseClass?->name,
                'class_id' => $enrollment->course_class_id,
                'status' => $enrollment->status?->value,
                'status_label' => $enrollment->status?->label(),
                'price_amount' => $enrollment->price_amount,
                'created_at' => $enrollment->created_at?->toIso8601String(),
            ],
        );

        $actor = $request->user();

        return Inertia::render('Admin/CourseEnrollments/Index', [
            'enrollments' => $enrollments,
            'filters' => ['search' => $search, 'status' => $status, 'per_page' => $perPage],
            'statusOptions' => AdminEnumOptions::fromEnum(EnrollmentStatus::class),
            'abilities' => [
                'create' => $actor?->can('enrollments-create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('enrollments-create'), 403);

        $selectedCourseId = (int) $request->query('course_id', 0);
        $selectedClassId = (int) $request->query('course_class_id', 0);

        $courses = Course::query()
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Course $course) => ['value' => $course->id, 'label' => $course->title])
            ->values()
            ->all();

        $classes = [];
        $defaultPrice = 0;

        if ($selectedCourseId > 0) {
            $classes = CourseClass::query()
                ->where('course_id', $selectedCourseId)
                ->orderBy('name')
                ->get(['id', 'name', 'price'])
                ->map(fn (CourseClass $class) => [
                    'value' => $class->id,
                    'label' => $class->name,
                    'price' => (int) ($class->price ?? 0),
                ])
                ->values()
                ->all();

            if ($selectedClassId > 0) {
                $defaultPrice = (int) (CourseClass::query()
                    ->where('course_id', $selectedCourseId)
                    ->whereKey($selectedClassId)
                    ->value('price') ?? 0);
            }
        }

        return Inertia::render('Admin/CourseEnrollments/Create', [
            'courses' => $courses,
            'classes' => $classes,
            'userOptions' => EnrollableUserOptions::forSelect(),
            'enrollmentStatusOptions' => AdminEnumOptions::fromEnum(EnrollmentStatus::class),
            'selectedCourseId' => $selectedCourseId > 0 ? $selectedCourseId : null,
            'selectedClassId' => $selectedClassId > 0 ? $selectedClassId : null,
            'defaultPrice' => $defaultPrice,
        ]);
    }

    public function store(
        StoreCourseEnrollmentPageRequest $request,
        AdminEnrollmentService $enrollmentService,
    ): RedirectResponse {
        $courseClass = $request->courseClass();
        $data = $request->validated();

        $enrollment = $enrollmentService->createForClass($courseClass, $data);

        return redirect()
            ->route('admin.course-enrollments.index')
            ->with('success', "ثبت‌نام #{$enrollment->id} برای «{$request->course()->title}» ایجاد شد.");
    }
}

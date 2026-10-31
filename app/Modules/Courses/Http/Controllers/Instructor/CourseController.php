<?php

namespace App\Modules\Courses\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseLevel;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Http\Requests\Admin\StoreCourseRequest;
use App\Modules\Courses\Http\Requests\Admin\UpdateCourseRequest;
use App\Modules\Assessments\Models\Quiz;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Support\AdminEnumOptions;
use App\Modules\Courses\Support\CourseClassMeetingSchedule;
use App\Modules\Courses\Support\CoursePresenter;
use App\Modules\Courses\Support\CourseOwnership;
use App\Modules\Courses\Support\InstructorCourseSubmission;
use App\Support\Notifications\InstructorModerationNotifier;
use App\Modules\Files\Http\Concerns\SyncsMorphFilesFromRequest;
use App\Support\SeoSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    use SyncsMorphFilesFromRequest;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Course::class);

        $user = $request->user();
        abort_if($user === null, 403);

        $courses = CourseOwnership::scopeForManager(Course::query(), $user)
            ->withCount('courseClasses')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Course $course) => CoursePresenter::forList($course));

        return Inertia::render('Instructor/Courses/Index', [
            'courses' => $courses,
            'canCreate' => $user->can('create', Course::class),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Course::class);

        return Inertia::render('Instructor/Courses/Create', $this->formOptions());
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $data = InstructorCourseSubmission::normalizeStatusForInstructor($request->validated());
        $data['is_featured'] = false;
        $data['display_order'] = $data['display_order'] ?? 0;

        $slug = $this->uniqueCourseSlug($data['slug'] ?? $data['title']);

        $course = Course::query()->create([
            'title' => $data['title'],
            'slug' => $slug,
            'code' => $data['code'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical' => $data['canonical'] ?? null,
            'level' => $data['level'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'status' => $data['status'],
            'is_featured' => false,
            'display_order' => (int) ($data['display_order'] ?? 0),
            'published_at' => $data['published_at'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        $course->categories()->sync($data['category_ids'] ?? []);
        $this->syncGalleryFromRequest($course, $request);

        if ($course->status === CourseStatus::PendingReview) {
            InstructorModerationNotifier::courseSubmittedForReview($course);
        }

        return redirect()
            ->route('instructor.courses.edit', $course)
            ->with('success', 'دوره ایجاد شد.');
    }

    public function show(Request $request, Course $course): Response
    {
        $this->authorize('view', $course);

        $classes = $course->courseClasses()
            ->withCount(['classSessions', 'enrollments'])
            ->orderBy('id')
            ->get()
            ->map(function (CourseClass $class) use ($course): array {
                $meeting = CourseClassMeetingSchedule::forClass($class);

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'code' => $class->code,
                    'status_label' => $class->status?->label() ?? '',
                    'sessions_count' => (int) ($class->class_sessions_count ?? 0),
                    'enrollments_count' => (int) ($class->enrollments_count ?? 0),
                    'meeting_summary_label' => $meeting['summary_label'],
                    'edit_url' => route('instructor.courses.classes.edit', [$course, $class]),
                    'sessions_url' => route('instructor.courses.classes.sessions.index', [$course, $class]),
                ];
            })
            ->values()
            ->all();

        $classIds = $course->courseClasses()->pluck('id');
        $sessionsTotal = $course->courseClasses()
            ->withCount('classSessions')
            ->get()
            ->sum('class_sessions_count');

        return Inertia::render('Instructor/Courses/Show', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'status_label' => $course->status?->label() ?? '',
                'edit_url' => route('instructor.courses.edit', $course),
                'classes_url' => route('instructor.courses.classes.index', $course),
                'roster_url' => route('instructor.courses.roster', $course),
                'quizzes_url' => route('instructor.courses.quizzes.index', $course),
            ],
            'classes' => $classes,
            'stats' => [
                'classes_total' => count($classes),
                'sessions_total' => (int) $sessionsTotal,
                'enrollments_confirmed' => Enrollment::query()
                    ->whereIn('course_class_id', $classIds)
                    ->where('status', EnrollmentStatus::Confirmed)
                    ->count(),
                'quizzes_total' => Quiz::query()->where('course_id', $course->id)->count(),
            ],
        ]);
    }

    public function edit(Request $request, Course $course): Response
    {
        $this->authorize('update', $course);

        $course->load('categories');

        return Inertia::render('Instructor/Courses/Edit', [
            'course' => CoursePresenter::forForm($course),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $data = InstructorCourseSubmission::normalizeStatusForInstructor($request->validated());
        $data['is_featured'] = $course->is_featured;
        $previousStatus = $course->status;

        $slugSource = filled($data['slug'] ?? null)
            ? (string) $data['slug']
            : ($course->slug ?: $data['title']);
        $slug = $this->uniqueCourseSlug($slugSource, $course->id);

        $course->update([
            'title' => $data['title'],
            'slug' => $slug,
            'code' => $data['code'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical' => $data['canonical'] ?? null,
            'level' => $data['level'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'status' => $data['status'],
            'is_featured' => $course->is_featured,
            'display_order' => $course->display_order,
            'published_at' => $data['published_at'] ?? null,
        ]);

        $course->categories()->sync($data['category_ids'] ?? []);
        $this->syncGalleryFromRequest($course, $request);

        $course->refresh();

        if (
            $course->status === CourseStatus::PendingReview
            && $previousStatus !== CourseStatus::PendingReview
        ) {
            InstructorModerationNotifier::courseSubmittedForReview($course);
        }

        return redirect()
            ->route('instructor.courses.edit', $course)
            ->with('success', 'دوره به‌روزرسانی شد.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $categories = Category::query()
            ->where('type', Category::TYPE_COURSE)
            ->orderBy('sort')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Category $category) => [
                'value' => $category->id,
                'label' => $category->title,
            ])
            ->values()
            ->all();

        return [
            'statusOptions' => InstructorCourseSubmission::statusOptionsForInstructor(),
            'levelOptions' => AdminEnumOptions::fromEnum(CourseLevel::class),
            'categoryOptions' => $categories,
        ];
    }

    private function uniqueCourseSlug(string $slug, ?int $ignoreId = null): string
    {
        $slug = SeoSlug::normalize($slug);
        if ($slug === '') {
            $slug = 'course';
        }

        $baseSlug = $slug;
        $counter = 1;

        while (Course::query()
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }
}

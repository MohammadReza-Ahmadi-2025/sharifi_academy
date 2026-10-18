<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\InstructorModerationQueue;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseLevel;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Http\Requests\Admin\StoreCourseRequest;
use App\Modules\Courses\Http\Requests\Admin\UpdateCourseRequest;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\AdminEnumOptions;
use App\Modules\Courses\Support\CourseOwnership;
use App\Modules\Courses\Support\CoursePresenter;
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

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = CourseOwnership::scopeForManager(Course::query(), $user)
            ->with('creator')
            ->withCount('courseClasses')
            ->orderBy('display_order')
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($status !== '' && in_array($status, CourseStatus::values(), true)) {
            $query->where('status', $status);
        }

        $courses = $query->paginate($perPage)->withQueryString()
            ->through(fn (Course $course) => CoursePresenter::forList($course));

        $actor = $request->user();

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
            ],
            'statusOptions' => AdminEnumOptions::fromEnum(CourseStatus::class),
            'abilities' => [
                'create' => $actor?->can('create', Course::class) ?? false,
                'update' => $actor?->can('courses-update') ?? false,
                'delete' => $actor?->can('courses-delete') ?? false,
                'moderate' => $actor?->can('instructor-moderation-update') ?? false,
            ],
            'pendingReviewCount' => $actor?->can('instructor-moderation-index')
                ? InstructorModerationQueue::counts()['courses_pending']
                : 0,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Course::class);

        return Inertia::render('Admin/Courses/Create', $this->formOptions());
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $data = $request->validated();
        $data = $this->normalizePrivilegedCourseFields($request, $data);
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
            'is_featured' => $data['is_featured'] ?? false,
            'display_order' => $data['display_order'] ?? 0,
            'published_at' => $data['published_at'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        $course->categories()->sync($data['category_ids'] ?? []);
        $this->syncGalleryFromRequest($course, $request);

        return redirect()
            ->route('admin.courses.show', $course)
            ->with('success', 'دوره ایجاد شد.');
    }

    public function show(Request $request, Course $course): Response
    {
        $this->authorize('view', $course);

        $course->load(['categories', 'creator'])->loadCount('courseClasses');

        $actor = $request->user();

        return Inertia::render('Admin/Courses/Show', [
            'course' => CoursePresenter::forShow($course),
            'abilities' => [
                'update' => $actor?->can('update', $course) ?? false,
                'delete' => $actor?->can('delete', $course) ?? false,
                'classes' => ($actor?->can('view', $course) && $actor->can('course-classes-index')) ?? false,
            ],
        ]);
    }

    public function edit(Request $request, Course $course): Response
    {
        $this->authorize('update', $course);

        $course->load('categories');

        return Inertia::render('Admin/Courses/Edit', [
            'course' => CoursePresenter::forForm($course),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $data = $request->validated();
        $data = $this->normalizePrivilegedCourseFields($request, $data, $course);

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
            'is_featured' => $data['is_featured'] ?? false,
            'display_order' => array_key_exists('display_order', $data) ? (int) $data['display_order'] : $course->display_order,
            'published_at' => $data['published_at'] ?? null,
        ]);

        $course->categories()->sync($data['category_ids'] ?? []);
        $this->syncGalleryFromRequest($course, $request);

        return redirect()
            ->route('admin.courses.show', $course)
            ->with('success', 'دوره به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'دوره حذف شد.');
    }

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizePrivilegedCourseFields(Request $request, array $data, ?Course $course = null): array
    {
        $user = $request->user();

        if ($user !== null && ! CourseOwnership::managesAllCourses($user)) {
            $data['is_featured'] = $course?->is_featured ?? false;
            if (! array_key_exists('display_order', $data)) {
                $data['display_order'] = $course?->display_order ?? 0;
            }
        }

        return $data;
    }

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
            'statusOptions' => AdminEnumOptions::fromEnum(CourseStatus::class),
            'levelOptions' => AdminEnumOptions::fromEnum(CourseLevel::class),
            'categoryOptions' => $categories,
        ];
    }
}

<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseLevel;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Http\Requests\Admin\StoreCourseRequest;
use App\Modules\Courses\Http\Requests\Admin\UpdateCourseRequest;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Support\AdminEnumOptions;
use App\Modules\Courses\Support\CoursePresenter;
use App\Modules\Files\Http\Concerns\SyncsMorphFilesFromRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    use SyncsMorphFilesFromRequest;

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('courses-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Course::query()
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
                'create' => $actor?->can('courses-create') ?? false,
                'update' => $actor?->can('courses-update') ?? false,
                'delete' => $actor?->can('courses-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('courses-create'), 403);

        return Inertia::render('Admin/Courses/Create', $this->formOptions());
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = $data['slug'] ?? Str::slug($data['title']);
        $baseSlug = $slug;
        $counter = 1;

        while (Course::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $course = Course::query()->create([
            'title' => $data['title'],
            'slug' => $slug,
            'code' => $data['code'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
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
        abort_unless($request->user()?->can('courses-index'), 403);

        $course->load(['categories', 'creator'])->loadCount('courseClasses');

        $actor = $request->user();

        return Inertia::render('Admin/Courses/Show', [
            'course' => CoursePresenter::forShow($course),
            'abilities' => [
                'update' => $actor?->can('courses-update') ?? false,
                'delete' => $actor?->can('courses-delete') ?? false,
                'classes' => $actor?->can('course-classes-index') ?? false,
            ],
        ]);
    }

    public function edit(Request $request, Course $course): Response
    {
        abort_unless($request->user()?->can('courses-update'), 403);

        $course->load('categories');

        return Inertia::render('Admin/Courses/Edit', [
            'course' => CoursePresenter::forForm($course),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = $course->slug ?: Str::slug($data['title']);
        }

        $course->update([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'code' => $data['code'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'level' => $data['level'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'status' => $data['status'],
            'is_featured' => $data['is_featured'] ?? false,
            'display_order' => $data['display_order'] ?? 0,
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
        abort_unless($request->user()?->can('courses-delete'), 403);

        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'دوره حذف شد.');
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
            'statusOptions' => AdminEnumOptions::fromEnum(CourseStatus::class),
            'levelOptions' => AdminEnumOptions::fromEnum(CourseLevel::class),
            'categoryOptions' => $categories,
        ];
    }
}

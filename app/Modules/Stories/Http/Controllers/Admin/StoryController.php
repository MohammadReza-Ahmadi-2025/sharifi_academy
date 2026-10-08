<?php

namespace App\Modules\Stories\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Http\Requests\Admin\StoreAdminStoryRequest;
use App\Modules\Stories\Http\Requests\Admin\UpdateAdminStoryRequest;
use App\Modules\Stories\Models\Story;
use App\Modules\Stories\Services\AdminStoryWriter;
use App\Modules\Stories\Services\StoryLifecycleService;
use App\Modules\Stories\Support\AdminStoryEnumOptions;
use App\Modules\Stories\Support\StoryPresenter;
use App\Modules\Stories\Support\StorySchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class StoryController extends Controller
{
    public function __construct(
        private readonly StoryLifecycleService $lifecycleService,
        private readonly AdminStoryWriter $writer,
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('stories-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Story::query()->with('user')->latest();

        if ($status !== '' && StoryStatus::tryFrom($status) !== null) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        $stories = $query->paginate($perPage)->withQueryString()
            ->through(fn (Story $story) => StoryPresenter::forAdminList($story));

        $actor = $request->user();

        return Inertia::render('Admin/Stories/Index', [
            'stories' => $stories,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
            ],
            'statusOptions' => AdminStoryEnumOptions::statusOptions(),
            'abilities' => [
                'create' => $actor?->can('stories-create') ?? false,
                'update' => $actor?->can('stories-update') ?? false,
                'moderate' => $actor?->can('stories-moderate') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('stories-create'), 403);

        return Inertia::render('Admin/Stories/Create', [
            ...$this->formOptions(),
            'defaultDisplayStartsAt' => StorySchedule::toFormValue(Carbon::now()),
        ]);
    }

    public function store(StoreAdminStoryRequest $request): RedirectResponse
    {
        $this->writer->create(
            $request->validated(),
            (int) $request->user()->id,
            $request->file('media'),
            $request->file('thumbnail'),
        );

        return redirect()
            ->route('admin.stories.index')
            ->with('success', 'استوری با موفقیت ایجاد شد.');
    }

    public function edit(Request $request, Story $story): Response
    {
        abort_unless($request->user()?->can('stories-update'), 403);

        return Inertia::render('Admin/Stories/Edit', [
            'story' => StoryPresenter::forAdminForm($story),
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateAdminStoryRequest $request, Story $story): RedirectResponse
    {
        $this->writer->update(
            $story,
            $request->validated(),
            $request->file('media'),
            $request->file('thumbnail'),
        );

        return redirect()
            ->route('admin.stories.index')
            ->with('success', 'استوری به‌روزرسانی شد.');
    }

    public function remove(Request $request, Story $story): RedirectResponse
    {
        abort_unless($request->user()?->can('stories-moderate'), 403);

        $this->lifecycleService->markRemoved($story);

        return back()->with('success', 'استوری به‌عنوان حذف‌شده ثبت شد.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'typeOptions' => AdminStoryEnumOptions::typeOptions(),
            'visibilityOptions' => AdminStoryEnumOptions::visibilityOptions(),
            'mediaLimits' => [
                'max_video_mb' => (int) config('stories.max_video_mb', 5),
                'max_image_mb' => (int) config('stories.max_image_mb', 10),
            ],
        ];
    }
}

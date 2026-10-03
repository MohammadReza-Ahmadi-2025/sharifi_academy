<?php

namespace App\Modules\Stories\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Models\Story;
use App\Modules\Stories\Services\StoryLifecycleService;
use App\Modules\Stories\Support\AdminStoryEnumOptions;
use App\Modules\Stories\Support\StoryPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoryController extends Controller
{
    public function __construct(
        private readonly StoryLifecycleService $lifecycleService,
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
            $query->whereHas('user', function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $stories = $query->paginate($perPage)->withQueryString()
            ->through(fn (Story $story) => StoryPresenter::forAdminList($story));

        return Inertia::render('Admin/Stories/Index', [
            'stories' => $stories,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
            ],
            'statusOptions' => AdminStoryEnumOptions::statusOptions(),
            'abilities' => [
                'moderate' => $request->user()?->can('stories-moderate') ?? false,
            ],
        ]);
    }

    public function remove(Request $request, Story $story): RedirectResponse
    {
        abort_unless($request->user()?->can('stories-moderate'), 403);

        $this->lifecycleService->markRemoved($story);

        return back()->with('success', 'استوری به‌عنوان حذف‌شده ثبت شد.');
    }
}

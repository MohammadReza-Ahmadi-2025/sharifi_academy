<?php

namespace App\Modules\Stories\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Enums\StoryVisibility;
use App\Modules\Stories\Http\Requests\StoreStoryRequest;
use App\Modules\Stories\Models\Story;
use App\Modules\Stories\Services\StoryLifecycleService;
use App\Modules\Stories\Services\StoryMediaService;
use App\Modules\Stories\Services\StoryViewService;
use App\Modules\Stories\Support\StoryPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoryController extends Controller
{
    public function __construct(
        private readonly StoryMediaService $mediaService,
        private readonly StoryLifecycleService $lifecycleService,
        private readonly StoryViewService $viewService,
    ) {}

    public function feed(Request $request): JsonResponse
    {
        $viewer = $request->user();

        $stories = Story::query()
            ->publishedActive()
            ->visibleTo($viewer)
            ->with(['file', 'user'])
            ->orderByDesc('published_at')
            ->get();

        $grouped = $stories
            ->groupBy('user_id')
            ->map(function ($items) use ($viewer) {
                return [
                    'user_id' => $items->first()->user_id,
                    'user_name' => $items->first()->user?->name,
                    'stories' => $items->map(fn (Story $story) => StoryPresenter::forFeed($story, $viewer))->values(),
                ];
            })
            ->values();

        return response()->json(['groups' => $grouped]);
    }

    public function store(StoreStoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $story = DB::transaction(function () use ($data, $user, $request) {
            $story = Story::query()->create([
                'user_id' => $user->id,
                'type' => $data['type'],
                'content' => $data['content'] ?? null,
                'background_color' => $data['background_color'] ?? null,
                'status' => StoryStatus::Draft,
                'visibility' => $data['visibility'] ?? StoryVisibility::Authenticated,
            ]);

            $upload = $request->file('media');

            if ($upload !== null) {
                $mimeType = $story->type === StoryType::Video ? 'story_video' : 'story_image';
                $this->mediaService->attachUpload($story, $upload, $mimeType);
            }

            return $this->lifecycleService->publish($story->fresh());
        });

        return response()->json([
            'story' => StoryPresenter::forFeed($story, $user),
        ], 201);
    }

    public function recordView(Request $request, Story $story): JsonResponse
    {
        $viewer = $request->user();
        abort_unless($viewer !== null, 401);

        $this->lifecycleService->expireIfNeeded($story);

        abort_unless($story->status === StoryStatus::Published, 404);

        $view = $this->viewService->record($story, $viewer);

        return response()->json([
            'view_count' => $view->view_count,
            'last_viewed_at' => $view->last_viewed_at?->toIso8601String(),
        ]);
    }

    public function destroy(Request $request, Story $story): JsonResponse
    {
        abort_unless($request->user()?->id === $story->user_id, 403);

        $this->lifecycleService->archive($story);

        return response()->json(['message' => 'استوری بایگانی شد.']);
    }
}

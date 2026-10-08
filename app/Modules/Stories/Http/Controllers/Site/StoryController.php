<?php

namespace App\Modules\Stories\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Models\Story;
use App\Modules\Stories\Services\StoryFeedService;
use App\Modules\Stories\Services\StoryLifecycleService;
use App\Modules\Stories\Services\StoryViewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function __construct(
        private readonly StoryLifecycleService $lifecycleService,
        private readonly StoryViewService $viewService,
        private readonly StoryFeedService $feedService,
    ) {}

    public function feed(Request $request): JsonResponse
    {
        return response()->json([
            'groups' => $this->feedService->groupsForViewer($request->user()),
        ]);
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
}

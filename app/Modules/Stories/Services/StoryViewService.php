<?php

namespace App\Modules\Stories\Services;

use App\Models\User;
use App\Modules\Stories\Models\Story;
use App\Modules\Stories\Models\StoryView;
use Illuminate\Support\Facades\DB;

class StoryViewService
{
    public function record(Story $story, User $viewer): StoryView
    {
        return DB::transaction(function () use ($story, $viewer) {
            $now = now();

            $view = StoryView::query()
                ->where('story_id', $story->id)
                ->where('user_id', $viewer->id)
                ->lockForUpdate()
                ->first();

            if ($view === null) {
                return StoryView::query()->create([
                    'story_id' => $story->id,
                    'user_id' => $viewer->id,
                    'first_viewed_at' => $now,
                    'last_viewed_at' => $now,
                    'view_count' => 1,
                ]);
            }

            $view->update([
                'last_viewed_at' => $now,
                'view_count' => (int) $view->view_count + 1,
            ]);

            return $view->fresh();
        });
    }
}

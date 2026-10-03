<?php

namespace App\Modules\Stories\Services;

use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Models\Story;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class StoryLifecycleService
{
    public function publish(Story $story): Story
    {
        if ($story->status === StoryStatus::Removed) {
            throw ValidationException::withMessages([
                'story' => 'این استوری قابل انتشار نیست.',
            ]);
        }

        if ($story->type !== StoryType::Text && $story->file_id === null) {
            throw ValidationException::withMessages([
                'file' => 'برای استوری تصویری یا ویدیویی فایل الزامی است.',
            ]);
        }

        if ($story->type === StoryType::Text && ! filled($story->content)) {
            throw ValidationException::withMessages([
                'content' => 'متن استوری الزامی است.',
            ]);
        }

        $publishedAt = now();
        $ttlHours = max(1, (int) config('stories.ttl_hours', 24));

        $story->update([
            'status' => StoryStatus::Published,
            'published_at' => $publishedAt,
            'expires_at' => $publishedAt->copy()->addHours($ttlHours),
        ]);

        return $story->fresh(['file', 'user']);
    }

    public function archive(Story $story): Story
    {
        $story->update(['status' => StoryStatus::Archived]);

        return $story->fresh();
    }

    public function markRemoved(Story $story): Story
    {
        $story->update(['status' => StoryStatus::Removed]);

        return $story->fresh();
    }

    public function expireIfNeeded(Story $story): void
    {
        if ($story->status !== StoryStatus::Published) {
            return;
        }

        if ($story->expires_at instanceof Carbon && $story->expires_at->isPast()) {
            $story->update(['status' => StoryStatus::Archived]);
        }
    }
}

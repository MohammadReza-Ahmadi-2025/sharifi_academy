<?php

namespace App\Modules\Stories\Support;

use App\Models\User;
use App\Modules\Stories\Models\Story;
use App\Modules\Stories\Models\StoryView;

class StoryPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forFeed(Story $story, ?User $viewer = null): array
    {
        $story->loadMissing(['file', 'user']);

        $viewed = false;

        if ($viewer !== null) {
            $viewed = StoryView::query()
                ->where('story_id', $story->id)
                ->where('user_id', $viewer->id)
                ->exists();
        }

        return [
            'id' => $story->id,
            'user_id' => $story->user_id,
            'user_name' => $story->user?->name,
            'type' => $story->type->value,
            'content' => $story->content,
            'background_color' => $story->background_color,
            'visibility' => $story->visibility->value,
            'published_at' => $story->published_at?->toIso8601String(),
            'expires_at' => $story->expires_at?->toIso8601String(),
            'file_url' => $story->file?->publicUrl(),
            'viewed' => $viewed,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forAdminList(Story $story): array
    {
        $story->loadMissing(['user']);

        return [
            'id' => $story->id,
            'user_name' => $story->user?->name,
            'type' => $story->type->value,
            'type_label' => $story->type->label(),
            'status' => $story->status->value,
            'status_label' => $story->status->label(),
            'visibility' => $story->visibility->value,
            'published_at' => $story->published_at?->format('Y-m-d H:i'),
            'expires_at' => $story->expires_at?->format('Y-m-d H:i'),
            'created_at' => $story->created_at?->format('Y-m-d H:i'),
        ];
    }
}

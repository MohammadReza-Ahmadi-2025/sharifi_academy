<?php

namespace App\Modules\Stories\Services;

use App\Models\User;
use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Models\Story;
use App\Modules\Stories\Models\StoryView;
use App\Modules\Stories\Support\StoryPresenter;
use App\Modules\Users\Support\UserAvatarMedia;
use App\Support\Storage\PublicStorageUrl;
use Illuminate\Support\Collection;

class StoryFeedService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function groupsForViewer(?User $viewer): array
    {
        $stories = Story::query()
            ->publishedActive()
            ->visibleTo($viewer)
            ->with(['file', 'thumbnail', 'user'])
            ->orderByDesc('published_at')
            ->get();

        if ($stories->isEmpty()) {
            return [];
        }

        $viewedStoryIds = $this->viewedStoryIdsForUser($stories, $viewer);

        return $stories
            ->groupBy('user_id')
            ->map(function (Collection $items) use ($viewer, $viewedStoryIds) {
                $first = $items->first();
                $user = $first->user;
                $mappedStories = $items->map(function (Story $story) use ($viewer, $viewedStoryIds) {
                    $viewed = $viewedStoryIds->contains($story->id);

                    return StoryPresenter::forFeed($story, $viewer, $viewed);
                })->values();

                $hasUnseen = $mappedStories->contains(fn (array $story) => $story['viewed'] === false);

                $avatar = UserAvatarMedia::payload($user);

                $avatarUrl = isset($avatar['url']) ? PublicStorageUrl::normalize($avatar['url']) : null;

                $headline = (string) ($mappedStories->first()['title'] ?? '');
                if ($headline === '') {
                    $headline = (string) ($user?->name ?? 'استوری');
                }

                return [
                    'user_id' => $first->user_id,
                    'user_name' => $user?->name,
                    'user_avatar_url' => $avatarUrl !== '' ? $avatarUrl : null,
                    'ring_title' => $headline,
                    'preview_url' => $this->previewImageUrlForGroup($items),
                    'has_unseen' => $hasUnseen,
                    'stories' => $mappedStories,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Story>  $stories
     * @return Collection<int, int>
     */
    private function viewedStoryIdsForUser(Collection $stories, ?User $viewer): Collection
    {
        if ($viewer === null) {
            return collect();
        }

        return StoryView::query()
            ->where('user_id', $viewer->id)
            ->whereIn('story_id', $stories->pluck('id'))
            ->pluck('story_id');
    }

    /**
     * @param  Collection<int, Story>  $items
     */
    /**
     * تصویر بند انگشتی حلقه — فقط استوری تصویری (نه ویدیو).
     */
    private function previewImageUrlForGroup(Collection $items): ?string
    {
        foreach ($items as $story) {
            if ($story->thumbnail !== null) {
                $url = $story->thumbnail->publicUrl();

                if ($url !== '') {
                    return $url;
                }
            }
        }

        foreach ($items as $story) {
            if ($story->type === StoryType::Image && $story->file !== null) {
                $url = $story->file->publicUrl();

                if ($url !== '') {
                    return $url;
                }
            }
        }

        return null;
    }
}

<?php

namespace App\Modules\Stories\Services;

use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Enums\StoryVisibility;
use App\Modules\Stories\Models\Story;
use App\Modules\Stories\Support\StorySchedule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class AdminStoryWriter
{
    public function __construct(
        private readonly StoryMediaService $mediaService,
        private readonly StoryLifecycleService $lifecycleService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $authorId, ?UploadedFile $media, ?UploadedFile $thumbnail): Story
    {
        return DB::transaction(function () use ($data, $authorId, $media, $thumbnail) {
            [$startsAt, $endsAt] = StorySchedule::fromRequestData($data);

            $story = Story::query()->create([
                'user_id' => $authorId,
                'title' => $data['title'],
                'type' => $data['type'],
                'content' => $data['content'] ?? null,
                'background_color' => $data['background_color'] ?? null,
                'status' => StoryStatus::Draft,
                'visibility' => $data['visibility'] ?? StoryVisibility::Authenticated,
                'published_at' => $startsAt,
                'expires_at' => $endsAt,
            ]);

            $this->syncMedia($story, $media, $thumbnail);

            if ($data['publish_now'] ?? false) {
                return $this->lifecycleService->publish($story->fresh(['file', 'thumbnail', 'user']));
            }

            return $story->fresh(['file', 'thumbnail', 'user']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Story $story, array $data, ?UploadedFile $media, ?UploadedFile $thumbnail): Story
    {
        return DB::transaction(function () use ($story, $data, $media, $thumbnail) {
            [$startsAt, $endsAt] = StorySchedule::fromRequestData($data);

            $story->update([
                'title' => $data['title'],
                'type' => $data['type'],
                'content' => $data['content'] ?? null,
                'background_color' => $data['background_color'] ?? null,
                'visibility' => $data['visibility'] ?? StoryVisibility::Authenticated,
                'published_at' => $startsAt,
                'expires_at' => $endsAt,
            ]);

            $this->syncMedia($story, $media, $thumbnail);

            $story = $story->fresh(['file', 'thumbnail', 'user']);

            if ($data['publish_now'] ?? false) {
                return $this->lifecycleService->publish($story);
            }

            return $story;
        });
    }

    private function syncMedia(Story $story, ?UploadedFile $media, ?UploadedFile $thumbnail): void
    {
        if ($media !== null) {
            $mimeType = $story->type === StoryType::Video ? 'story_video' : 'story_image';
            $this->mediaService->attachUpload($story, $media, $mimeType);
        }

        if ($thumbnail !== null) {
            $this->mediaService->attachThumbnail($story, $thumbnail);
        }
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Enums\StoryVisibility;
use App\Modules\Stories\Models\Story;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminStoriesTest extends TestCase
{
    use RefreshDatabase;

    private function storySchedulePayload(): array
    {
        return [
            'display_starts_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'display_ends_at' => '',
        ];
    }

    public function test_admin_can_create_edit_and_list_stories(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/stories/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Stories/Create'));

        $this->actingAs($admin)
            ->post('/admin/stories', [
                'title' => 'استوری تست',
                'type' => StoryType::Text->value,
                'content' => 'استوری ادمین',
                'background_color' => '#123456',
                'visibility' => StoryVisibility::Public->value,
                'publish_now' => true,
                ...$this->storySchedulePayload(),
            ])
            ->assertRedirect(route('admin.stories.index'));

        $story = Story::query()->where('title', 'استوری تست')->firstOrFail();
        $this->assertSame(StoryStatus::Published, $story->status);
        $this->assertSame($admin->id, $story->user_id);

        $this->actingAs($admin)
            ->get("/admin/stories/{$story->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Stories/Edit')
                ->where('story.id', $story->id)
            );

        $this->actingAs($admin)
            ->put("/admin/stories/{$story->id}", [
                'title' => 'استوری تست',
                'type' => StoryType::Image->value,
                'content' => 'کپشن',
                'visibility' => StoryVisibility::Authenticated->value,
                'publish_now' => true,
                ...$this->storySchedulePayload(),
                'media' => UploadedFile::fake()->image('story.jpg'),
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
            ])
            ->assertRedirect(route('admin.stories.index'));

        $story->refresh();
        $this->assertSame(StoryType::Image, $story->type);
        $this->assertNotNull($story->file_id);
        $this->assertNotNull($story->thumbnail_file_id);
    }

    public function test_video_larger_than_five_megabytes_is_rejected(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/stories', [
                'title' => 'ویدیو بزرگ',
                'type' => StoryType::Video->value,
                'visibility' => StoryVisibility::Public->value,
                'publish_now' => true,
                ...$this->storySchedulePayload(),
                'media' => UploadedFile::fake()->create('big.mp4', 5121, 'video/mp4'),
            ])
            ->assertSessionHasErrors('media');
    }

    public function test_admin_can_create_video_story(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/stories', [
                'title' => 'ویدیو کوتاه',
                'type' => StoryType::Video->value,
                'visibility' => StoryVisibility::Public->value,
                'publish_now' => true,
                ...$this->storySchedulePayload(),
                'media' => UploadedFile::fake()->create('clip.mp4', 512, 'video/mp4'),
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
            ])
            ->assertRedirect(route('admin.stories.index'));

        $story = Story::query()->where('title', 'ویدیو کوتاه')->firstOrFail();
        $this->assertSame(StoryStatus::Published, $story->status);
        $this->assertSame($admin->id, $story->user_id);
        $this->assertNotNull($story->file_id);
    }
}

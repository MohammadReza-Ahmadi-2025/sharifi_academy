<?php

namespace Tests\Feature\Stories;

use App\Models\User;
use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Enums\StoryVisibility;
use App\Modules\Stories\Models\Story;
use App\Modules\Stories\Models\StoryView;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_story_creation_is_not_available(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($user)
            ->post('/stories', [
                'type' => StoryType::Text->value,
                'content' => 'سلام استوری',
                'visibility' => StoryVisibility::Public->value,
            ])
            ->assertNotFound();
    }

    public function test_story_view_is_recorded_once_per_user_with_increment(): void
    {
        $this->seed(DatabaseSeeder::class);
        $author = User::where('mobile', '09120000001')->firstOrFail();
        $viewer = User::where('mobile', '09120000003')->firstOrFail();

        $story = Story::query()->create([
            'user_id' => $author->id,
            'title' => 'تست',
            'type' => StoryType::Text,
            'content' => 'تست',
            'status' => StoryStatus::Published,
            'visibility' => StoryVisibility::Authenticated,
            'published_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($viewer)->post('/stories/'.$story->id.'/views')->assertOk();
        $this->actingAs($viewer)->post('/stories/'.$story->id.'/views')->assertOk();

        $view = StoryView::query()->where('story_id', $story->id)->where('user_id', $viewer->id)->firstOrFail();
        $this->assertSame(2, $view->view_count);
    }

    public function test_feed_lists_public_stories_for_guests(): void
    {
        $user = User::factory()->create();

        Story::query()->create([
            'user_id' => $user->id,
            'title' => 'عمومی',
            'type' => StoryType::Text,
            'content' => 'عمومی',
            'status' => StoryStatus::Published,
            'visibility' => StoryVisibility::Public,
            'published_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        Story::query()->create([
            'user_id' => $user->id,
            'title' => 'خصوصی',
            'type' => StoryType::Text,
            'content' => 'خصوصی',
            'status' => StoryStatus::Published,
            'visibility' => StoryVisibility::Authenticated,
            'published_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->getJson('/stories/feed');
        $response->assertOk();

        $ids = collect($response->json('groups.0.stories'))->pluck('content');
        $this->assertTrue($ids->contains('عمومی'));
        $this->assertFalse($ids->contains('خصوصی'));
    }

    public function test_feed_ring_preview_does_not_use_video_file_url(): void
    {
        $user = User::factory()->create();

        Story::query()->create([
            'user_id' => $user->id,
            'title' => 'ویدیو',
            'type' => StoryType::Video,
            'content' => null,
            'status' => StoryStatus::Published,
            'visibility' => StoryVisibility::Public,
            'published_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $preview = $this->getJson('/stories/feed')->json('groups.0.preview_url');

        $this->assertNull($preview);
    }
}

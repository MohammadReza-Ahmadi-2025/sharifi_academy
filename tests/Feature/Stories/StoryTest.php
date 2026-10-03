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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_publish_text_story_and_it_expires_in_configured_ttl(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($user)
            ->post('/stories', [
                'type' => StoryType::Text->value,
                'content' => 'سلام استوری',
                'background_color' => '#112233',
                'visibility' => StoryVisibility::Public->value,
            ])
            ->assertCreated()
            ->assertJsonPath('story.type', StoryType::Text->value);

        $story = Story::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(StoryStatus::Published, $story->status);
        $this->assertNotNull($story->published_at);
        $this->assertNotNull($story->expires_at);
        $this->assertTrue($story->published_at->diffInHours($story->expires_at) === 24.0);
    }

    public function test_story_view_is_recorded_once_per_user_with_increment(): void
    {
        $this->seed(DatabaseSeeder::class);
        $author = User::where('mobile', '09120000001')->firstOrFail();
        $viewer = User::where('mobile', '09120000003')->firstOrFail();

        $story = Story::query()->create([
            'user_id' => $author->id,
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

    public function test_image_story_requires_media_file(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $user = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($user)
            ->post('/stories', [
                'type' => StoryType::Image->value,
                'visibility' => StoryVisibility::Authenticated->value,
            ])
            ->assertSessionHasErrors('media');

        $this->actingAs($user)
            ->post('/stories', [
                'type' => StoryType::Image->value,
                'content' => 'کپشن',
                'visibility' => StoryVisibility::Authenticated->value,
                'media' => UploadedFile::fake()->image('story.jpg'),
            ])
            ->assertCreated();

        $this->assertNotNull(Story::query()->where('user_id', $user->id)->value('file_id'));
    }

    public function test_feed_lists_public_stories_for_guests(): void
    {
        $user = User::factory()->create();

        Story::query()->create([
            'user_id' => $user->id,
            'type' => StoryType::Text,
            'content' => 'عمومی',
            'status' => StoryStatus::Published,
            'visibility' => StoryVisibility::Public,
            'published_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        Story::query()->create([
            'user_id' => $user->id,
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
}

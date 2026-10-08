<?php

namespace Tests\Feature\Stories;

use App\Models\User;
use App\Modules\Stories\Enums\StoryStatus;
use App\Modules\Stories\Enums\StoryType;
use App\Modules\Stories\Enums\StoryVisibility;
use App\Modules\Stories\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_before_start_date_is_hidden_from_feed(): void
    {
        $user = User::factory()->create();

        Story::query()->create([
            'user_id' => $user->id,
            'title' => 'آینده',
            'type' => StoryType::Text,
            'content' => 'آینده',
            'status' => StoryStatus::Published,
            'visibility' => StoryVisibility::Public,
            'published_at' => now()->addDay(),
            'expires_at' => null,
        ]);

        $this->getJson('/stories/feed')
            ->assertOk()
            ->assertJsonPath('groups', []);
    }

    public function test_story_without_end_date_stays_visible(): void
    {
        $user = User::factory()->create();

        Story::query()->create([
            'user_id' => $user->id,
            'title' => 'همیشگی',
            'type' => StoryType::Text,
            'content' => 'همیشگی',
            'status' => StoryStatus::Published,
            'visibility' => StoryVisibility::Public,
            'published_at' => now()->subHour(),
            'expires_at' => null,
        ]);

        $content = $this->getJson('/stories/feed')->json('groups.0.stories.0.content');
        $this->assertSame('همیشگی', $content);
    }
}

<?php

namespace Tests\Feature\Blog;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Comments\Enums\CommentStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_user_can_comment_on_published_post(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $post = Post::query()->create([
            'title' => 'مقاله تست',
            'slug' => 'test-post-comment',
            'content' => 'متن',
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($student)
            ->post("/blog/{$post->slug}/comments", [
                'body' => 'نظر خوبی بود',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('comments', [
            'commentable_type' => $post->getMorphClass(),
            'commentable_id' => $post->id,
            'user_id' => $student->id,
            'body' => 'نظر خوبی بود',
            'status' => CommentStatus::Pending->value,
        ]);
    }
}

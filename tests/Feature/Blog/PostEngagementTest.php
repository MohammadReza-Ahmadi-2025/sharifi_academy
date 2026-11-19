<?php

namespace Tests\Feature\Blog;

use App\Models\User;
use App\Modules\Blog\Enums\PostKind;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Enums\ReactionType;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\Reaction;
use App\Modules\Comments\Enums\CommentStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostEngagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function publishedCommunityPost(): Post
    {
        return Post::query()->create([
            'title' => 'پست تعاملی',
            'slug' => 'engagement-post',
            'content' => '<p>متن</p>',
            'status' => PostStatus::Published,
            'kind' => PostKind::Community,
            'published_at' => now()->subHour(),
        ]);
    }

    public function test_authenticated_user_can_comment_on_community_post(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $post = $this->publishedCommunityPost();

        $this->actingAs($student)
            ->post("/posts/{$post->id}/{$post->slug}/comments", [
                'body' => 'نظر روی پست کاربری',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('comments', [
            'commentable_type' => $post->getMorphClass(),
            'commentable_id' => $post->id,
            'user_id' => $student->id,
            'body' => 'نظر روی پست کاربری',
            'status' => CommentStatus::Pending->value,
        ]);
    }

    public function test_authenticated_user_can_toggle_reaction_on_community_post(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $post = $this->publishedCommunityPost();

        $this->actingAs($student)
            ->post("/posts/{$post->id}/{$post->slug}/reactions", [
                'type' => ReactionType::Love->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reactions', [
            'reactable_type' => $post->getMorphClass(),
            'reactable_id' => $post->id,
            'user_id' => $student->id,
            'type' => ReactionType::Love->value,
        ]);

        $this->actingAs($student)
            ->post("/posts/{$post->id}/{$post->slug}/reactions", [
                'type' => ReactionType::Like->value,
            ])
            ->assertRedirect();

        $this->assertSame(1, Reaction::query()->where('reactable_id', $post->id)->count());
        $this->assertSame(
            ReactionType::Like,
            Reaction::query()->where('user_id', $student->id)->firstOrFail()->type,
        );

        $this->actingAs($student)
            ->post("/posts/{$post->id}/{$post->slug}/reactions", [
                'type' => ReactionType::Like->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('reactions', [
            'reactable_id' => $post->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_guest_cannot_react_or_comment_on_community_post(): void
    {
        $post = $this->publishedCommunityPost();

        $this->post("/posts/{$post->id}/{$post->slug}/comments", [
            'body' => 'مهمان',
        ])->assertRedirect();

        $this->post("/posts/{$post->id}/{$post->slug}/reactions", [
            'type' => ReactionType::Like->value,
        ])->assertRedirect();

        $this->assertDatabaseMissing('comments', [
            'commentable_id' => $post->id,
            'body' => 'مهمان',
        ]);
        $this->assertDatabaseCount('reactions', 0);
    }

    public function test_community_post_show_includes_engagement_props(): void
    {
        $post = $this->publishedCommunityPost();

        $this->get("/posts/{$post->id}/{$post->slug}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Posts/Show')
                ->has('post.comments')
                ->has('post.comments_summary')
                ->has('post.reactions.types')
                ->where('post.reactions.total', 0));
    }

    public function test_feed_cards_include_inline_engagement_payload(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $post = $this->publishedCommunityPost();

        Reaction::query()->create([
            'reactable_type' => $post->getMorphClass(),
            'reactable_id' => $post->id,
            'user_id' => $student->id,
            'type' => ReactionType::Like,
        ]);

        $this->actingAs($student)
            ->get('/posts')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Posts/Index')
                ->has('posts.data.0.reactions.types')
                ->where('posts.data.0.reactions.total', 1)
                ->where('posts.data.0.reactions.viewer_type', ReactionType::Like->value)
                ->has('posts.data.0.comments_preview')
                ->has('posts.data.0.preview_html'));
    }
}

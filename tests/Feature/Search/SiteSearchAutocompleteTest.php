<?php

namespace Tests\Feature\Search;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSearchAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['scout.driver' => 'collection']);
    }

    public function test_autocomplete_requires_at_least_two_characters(): void
    {
        $this->getJson('/search/autocomplete?q=a')
            ->assertOk()
            ->assertJson(['items' => []]);
    }

    public function test_autocomplete_finds_published_course_via_database_fallback(): void
    {
        Course::withoutSyncingToSearch(function (): void {
            Course::query()->create([
                'title' => 'Unique searchable course',
                'slug' => 'unique-search-course',
                'status' => CourseStatus::Published,
                'published_at' => now(),
            ]);
        });

        $this->get('/search/autocomplete?q=unique')
            ->assertOk()
            ->assertJsonFragment([
                'kind' => 'course',
                'title' => 'Unique searchable course',
            ]);
    }

    public function test_autocomplete_finds_published_blog_post(): void
    {
        $this->seed(DatabaseSeeder::class);

        $author = User::where('mobile', '09120000001')->firstOrFail();

        Post::withoutSyncingToSearch(function () use ($author): void {
            Post::query()->create([
                'author_id' => $author->id,
                'title' => 'Special searchable post',
                'slug' => 'special-search-post',
                'excerpt' => 'چکیده',
                'content' => 'متن',
                'status' => PostStatus::Published,
                'published_at' => now()->subHour(),
            ]);
        });

        $this->get('/search/autocomplete?q=special')
            ->assertOk()
            ->assertJsonFragment([
                'kind' => 'post',
                'title' => 'Special searchable post',
            ]);
    }
}

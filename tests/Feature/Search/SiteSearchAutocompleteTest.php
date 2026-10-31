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

    public function test_autocomplete_finds_katia_when_query_has_first_letter_typo(): void
    {
        Course::withoutSyncingToSearch(function (): void {
            Course::query()->create([
                'title' => 'آموزش کتیا',
                'slug' => 'katia-typo-course',
                'status' => CourseStatus::Published,
                'published_at' => now(),
            ]);
        });

        $this->get('/search/autocomplete?q='.urlencode('گتیا'))
            ->assertOk()
            ->assertJsonFragment([
                'kind' => 'course',
                'title' => 'آموزش کتیا',
            ]);
    }

    public function test_search_results_page_groups_courses_and_posts(): void
    {
        $this->seed(DatabaseSeeder::class);
        $author = User::where('mobile', '09120000001')->firstOrFail();

        Course::withoutSyncingToSearch(function (): void {
            Course::query()->create([
                'title' => 'Results page course alpha',
                'slug' => 'results-page-course-alpha',
                'status' => CourseStatus::Published,
                'published_at' => now(),
            ]);
        });

        Post::withoutSyncingToSearch(function () use ($author): void {
            Post::query()->create([
                'author_id' => $author->id,
                'title' => 'Results page post alpha',
                'slug' => 'results-page-post-alpha',
                'excerpt' => 'چکیده',
                'content' => 'متن',
                'status' => PostStatus::Published,
                'published_at' => now()->subHour(),
            ]);
        });

        $this->get('/search?q=Results+page')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Search/Index')
                ->where('q', 'Results page')
                ->where('canonical', route('search.index'))
                ->where('total', 2)
                ->has('groups.course', 1)
                ->has('groups.post', 1)
                ->where('groups.course.0.title', 'Results page course alpha')
                ->where('groups.post.0.title', 'Results page post alpha'));
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

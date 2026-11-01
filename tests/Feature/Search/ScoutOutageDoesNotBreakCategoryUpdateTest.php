<?php

namespace Tests\Feature\Search;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScoutOutageDoesNotBreakCategoryUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_image_replace_succeeds_when_meilisearch_is_unreachable(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        config([
            'scout.driver' => 'meilisearch',
            'scout.meilisearch.host' => 'http://127.0.0.1:9',
            'scout.meilisearch.key' => null,
        ]);

        $category = Category::withoutSyncingToSearch(fn () => Category::query()->create([
            'type' => Category::TYPE_COURSE,
            'title' => 'دسته دوره تست',
            'slug' => 'course-cat-scout-outage',
            'status' => true,
            'show_in_menu' => true,
        ]));

        $this->actingAs($admin)->put('/admin/categories/'.$category->id, [
            'title' => 'دسته دوره تست',
            'status' => true,
            'show_in_menu' => true,
            'image' => [
                0 => [
                    'alt' => 'کاور',
                    'file' => UploadedFile::fake()->image('cover.jpg'),
                ],
            ],
        ])->assertRedirect();

        $this->assertSame(1, $category->fresh()->image()->count());
    }
}

<?php

namespace Tests\Feature\Catalog;

use App\Modules\Catalog\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_table_supports_nested_tree_and_json_meta_keywords(): void
    {
        $root = Category::query()->create([
            'title' => 'دوره‌ها',
            'slug' => 'courses',
            'type' => 'product',
            'meta_keywords' => ['لاراول', 'php'],
            'status' => 1,
            'sort' => 1,
        ]);

        $child = Category::query()->create([
            'parent_id' => $root->id,
            'title' => 'برنامه‌نویسی',
            'slug' => 'programming',
            'type' => 'product',
            'status' => 1,
            'sort' => 1,
        ]);

        $root->refresh();

        $this->assertCount(1, $root->children);
        $this->assertSame($child->id, $root->children->first()?->id);
        $this->assertSame(['لاراول', 'php'], $root->meta_keywords);
        $this->assertSame('courses', $root->root()->slug);
    }
}

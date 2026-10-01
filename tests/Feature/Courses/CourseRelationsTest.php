<?php

namespace Tests\Feature\Courses;

use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Models\Course;
use App\Modules\Files\Models\File;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_links_to_categories_and_files(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $category = Category::query()->create([
            'title' => 'برنامه‌نویسی',
            'slug' => 'programming-courses',
            'type' => Category::TYPE_COURSE,
            'status' => 1,
        ]);

        $course = Course::query()->create([
            'title' => 'لاراول پیشرفته',
            'slug' => 'laravel-advanced',
            'status' => CourseStatus::Draft,
            'created_by' => $admin->id,
        ]);

        $course->categories()->attach($category->id);

        $course->files()->create([
            'path' => 'courses/laravel-advanced/cover.jpg',
            'type' => 'gallery',
            'sort' => 1,
            'alt' => 'کاور دوره',
        ]);

        $course->refresh()->load(['categories', 'mainImage', 'gallery']);

        $this->assertCount(1, $course->categories);
        $this->assertSame(Category::TYPE_COURSE, $course->categories->first()?->type);
        $this->assertCount(1, $category->courses);
        $this->assertNotNull($course->mainImage);
        $this->assertCount(1, $course->gallery);
    }
}

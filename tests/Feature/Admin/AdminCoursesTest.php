<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminCoursesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_access_courses_index(): void
    {
        $this->seed(DatabaseSeeder::class);
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($student)->get('/admin/courses')->assertForbidden();
    }

    public function test_super_admin_can_manage_course_and_nested_class(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/courses')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Courses/Index'));

        $this->actingAs($admin)
            ->post('/admin/courses', [
                'title' => 'دوره تست ادمین',
                'status' => CourseStatus::Draft->value,
                'is_featured' => false,
                'gallery' => [
                    0 => ['file' => UploadedFile::fake()->image('course.jpg')],
                ],
            ])
            ->assertRedirect();

        $course = Course::query()->where('title', 'دوره تست ادمین')->firstOrFail();
        $course->load('gallery');
        $galleryFileId = $course->gallery->firstOrFail()->id;

        $this->actingAs($admin)
            ->get('/admin/courses/'.$course->id)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Courses/Show'));

        $category = Category::query()->create([
            'title' => 'آموزش',
            'slug' => 'edu-admin',
            'type' => Category::TYPE_COURSE,
            'status' => 1,
        ]);

        $this->actingAs($admin)
            ->put('/admin/courses/'.$course->id, [
                'title' => 'دوره تست ادمین',
                'status' => CourseStatus::Published->value,
                'is_featured' => true,
                'category_ids' => [$category->id],
                'gallery' => [
                    0 => ['id' => $galleryFileId],
                ],
            ])
            ->assertRedirect(route('admin.courses.show', $course))
            ->assertSessionHasNoErrors();

        $this->assertTrue($course->fresh()->categories()->whereKey($category->id)->exists());

        $this->actingAs($admin)
            ->post('/admin/courses/'.$course->id.'/classes', [
                'name' => 'گروه ۱',
                'code' => 'G1-ADMIN',
                'status' => 'upcoming',
                'delivery_type' => 'online',
                'is_public' => true,
                'price' => 2_000_000,
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get('/admin/courses/'.$course->id.'/classes')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Courses/Classes/Index')->has('classes', 1));
    }
}

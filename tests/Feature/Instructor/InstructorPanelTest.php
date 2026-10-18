<?php

namespace Tests\Feature\Instructor;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InstructorPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    public function test_instructor_can_open_panel_and_create_own_course(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();

        $category = Category::query()->create([
            'title' => 'زبان',
            'slug' => 'lang-instructor',
            'type' => Category::TYPE_COURSE,
            'status' => 1,
        ]);

        $this->actingAs($instructor)
            ->get('/instructor')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Instructor/Dashboard')
                ->has('stats.courses_total')
                ->has('stats.classes_total'));

        $this->actingAs($instructor)
            ->post('/instructor/courses', [
                'title' => 'دوره استاد نمونه',
                'status' => CourseStatus::Draft->value,
                'category_ids' => [$category->id],
                'gallery' => [
                    0 => ['file' => UploadedFile::fake()->image('course.jpg')],
                ],
            ])
            ->assertRedirect();

        $course = Course::query()->where('title', 'دوره استاد نمونه')->firstOrFail();
        $this->assertSame($instructor->id, $course->created_by);

        $this->actingAs($instructor)
            ->get('/instructor/courses/'.$course->id.'/edit')
            ->assertOk();
    }

    public function test_instructor_cannot_edit_another_users_course(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $course = Course::query()->create([
            'title' => 'دوره ادمین',
            'slug' => 'admin-course',
            'status' => CourseStatus::Draft,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($instructor)
            ->get('/instructor/courses/'.$course->id.'/edit')
            ->assertForbidden();
    }

    public function test_instructor_can_manage_own_resume(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();

        $this->actingAs($instructor)
            ->get('/instructor/resume')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Instructor/Resume/Index'));

        $this->actingAs($instructor)
            ->post('/instructor/resume', [
                'type' => 'education',
                'title' => 'کارشناسی زبان',
                'organization' => 'دانشگاه تهران',
                'is_current' => false,
            ])
            ->assertRedirect(route('instructor.resume.index'));

        $this->assertDatabaseHas('resumes', [
            'user_id' => $instructor->id,
            'title' => 'کارشناسی زبان',
        ]);
    }

    public function test_student_cannot_access_instructor_panel(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($student)->get('/instructor')->assertForbidden();
    }
}

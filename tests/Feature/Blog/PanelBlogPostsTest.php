<?php

namespace Tests\Feature\Blog;

use App\Models\User;
use App\Modules\Blog\Enums\PostKind;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Locations\Models\ProvinceCity;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PanelBlogPostsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        DefaultRolePermissions::sync();
    }

    private function completeProfile(User $user): void
    {
        $city = ProvinceCity::query()->firstOrFail();
        $user->forceFill([
            'name' => $user->name ?: 'کاربر',
            'family' => $user->family ?: 'تست',
        ])->save();
        $user->profile()->updateOrCreate([], [
            'province_city_id' => $city->id,
            'is_public' => true,
        ]);
    }

    private function confirmEnrollmentFor(User $user): void
    {
        $course = Course::query()->create([
            'title' => 'دوره تست پست',
            'slug' => 'panel-post-course-'.uniqid(),
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه A',
            'code' => 'PP-'.strtoupper(substr(uniqid(), -6)),
            'status' => CourseClassStatus::Upcoming,
            'price' => 0,
        ]);

        Enrollment::query()->create([
            'course_class_id' => $class->id,
            'user_id' => $user->id,
            'status' => EnrollmentStatus::Confirmed,
            'confirmed_at' => now(),
            'price_amount' => 0,
        ]);
    }

    public function test_student_can_create_pending_post_without_publishing(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $this->completeProfile($student);
        $this->confirmEnrollmentFor($student);
        $category = Category::query()->create([
            'type' => Category::TYPE_BLOG,
            'title' => 'دانشجویی',
            'slug' => 'student-blog-cat',
            'status' => true,
        ]);

        $this->actingAs($student)
            ->get('/student/posts')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Student/Blog/Posts/Index'));

        $this->actingAs($student)
            ->post('/student/posts', [
                'title' => 'پست دانشجو',
                'content' => "متن ساده بدون ادیتور\nخط دوم",
                'status' => PostStatus::PendingReview->value,
                'category_ids' => [$category->id],
                'featured' => [
                    0 => ['file' => UploadedFile::fake()->image('s.jpg')],
                ],
            ])
            ->assertRedirect();

        $post = Post::query()->where('title', 'پست دانشجو')->firstOrFail();
        $this->assertSame($student->id, $post->author_id);
        $this->assertSame(PostStatus::PendingReview, $post->status);
        $this->assertNull($post->published_at);

        $this->get('/blog/'.$post->id.'/'.$post->slug)->assertNotFound();

        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $this->actingAs($admin)
            ->put('/admin/posts/'.$post->id, [
                'title' => $post->title,
                'content' => $post->content,
                'status' => PostStatus::Published->value,
                'category_ids' => [$category->id],
                'featured' => [
                    0 => ['id' => $post->featuredImage?->id],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(PostStatus::Published, $post->fresh()->status);
        $this->get('/blog/'.$post->id.'/'.$post->slug)->assertRedirect('/posts/'.$post->id.'/'.$post->slug);
        $this->get('/posts/'.$post->id.'/'.$post->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Public/Posts/Show'));
    }

    public function test_instructor_can_manage_own_posts_only(): void
    {
        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $this->completeProfile($instructor);
        $other = User::where('mobile', '09120000003')->firstOrFail();
        $this->completeProfile($other);
        $category = Category::query()->create([
            'type' => Category::TYPE_BLOG,
            'title' => 'استادی',
            'slug' => 'instructor-blog-cat',
            'status' => true,
        ]);

        $foreign = Post::query()->create([
            'author_id' => $other->id,
            'title' => 'پست دیگری',
            'slug' => 'foreign-post',
            'content' => 'x',
            'status' => PostStatus::Draft,
            'kind' => PostKind::Community,
        ]);

        $this->actingAs($instructor)
            ->get('/instructor/posts/'.$foreign->id.'/edit')
            ->assertNotFound();

        $this->actingAs($instructor)
            ->post('/instructor/posts', [
                'title' => 'پست استاد',
                'content' => 'محتوا',
                'status' => PostStatus::Draft->value,
                'category_ids' => [$category->id],
                'featured' => [
                    0 => ['file' => UploadedFile::fake()->image('i.jpg')],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'title' => 'پست استاد',
            'author_id' => $instructor->id,
            'status' => PostStatus::Draft->value,
        ]);
    }

    public function test_panel_author_cannot_set_published_status(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $this->completeProfile($student);
        $this->confirmEnrollmentFor($student);
        $category = Category::query()->create([
            'type' => Category::TYPE_BLOG,
            'title' => 'محدود',
            'slug' => 'limited-blog-cat',
            'status' => true,
        ]);

        $this->actingAs($student)
            ->post('/student/posts', [
                'title' => 'تلاش انتشار مستقیم',
                'content' => 'متن',
                'status' => PostStatus::Published->value,
                'category_ids' => [$category->id],
                'featured' => [
                    0 => ['file' => UploadedFile::fake()->image('p.jpg')],
                ],
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_student_without_enrollment_sees_message_on_posts_index(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $this->completeProfile($student);

        $this->actingAs($student->fresh())
            ->get('/student/posts')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Student/Blog/Posts/Index')
                ->where('needs_enrollment', true)
                ->where('abilities.create', false));
    }

    public function test_student_without_enrollment_cannot_create_post(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $this->completeProfile($student);

        $this->actingAs($student)
            ->get('/student/posts/create')
            ->assertRedirect('/student/posts');

        $this->actingAs($student)
            ->post('/student/posts', [
                'title' => 'بدون ثبت‌نام',
                'content' => 'متن',
                'status' => PostStatus::Draft->value,
            ])
            ->assertNotFound();
    }

    public function test_panel_plain_text_escapes_manual_html_and_scripts(): void
    {
        $student = User::where('mobile', '09120000003')->firstOrFail();
        $this->completeProfile($student);
        $this->confirmEnrollmentFor($student);

        $response = $this->actingAs($student)
            ->post('/student/posts', [
                'title' => 'XSS تست',
                'excerpt' => '<script>alert(1)</script><img src=x onerror=alert(2)>',
                'content' => "خط امن\n\n<a href=\"javascript:alert(3)\">لینک</a><script>evil()</script>",
                'status' => PostStatus::Draft->value,
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect();

        $post = Post::query()->where('title', 'XSS تست')->firstOrFail();

        $excerpt = (string) $post->excerpt;
        $content = (string) $post->content;

        // Manual HTML/script is escaped to text entities, not kept as executable markup.
        $this->assertDoesNotMatchRegularExpression('/<script\b/i', $excerpt);
        $this->assertDoesNotMatchRegularExpression('/<script\b/i', $content);
        $this->assertDoesNotMatchRegularExpression('/<img\b/i', $excerpt);
        $this->assertDoesNotMatchRegularExpression('/<a\b/i', $content);
        $this->assertStringContainsString('&lt;script&gt;', $excerpt);
        $this->assertStringContainsString('&lt;script&gt;', $content);
        $this->assertStringContainsString('&lt;a href=', $content);
        $this->assertStringContainsString('onerror=', $excerpt);
    }
}

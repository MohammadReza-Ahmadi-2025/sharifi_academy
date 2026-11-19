<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Modules\Blog\Enums\PostKind;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Locations\Models\ProvinceCity;
use App\Modules\Profile\Support\InstructorPublicUuid;
use App\Modules\Profile\Support\UserPublicSlug;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicUserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_opens_by_uuid_with_seo_slug(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('mobile', '09120000003')->firstOrFail();
        $user->forceFill(['name' => 'سارا', 'family' => 'محمدی', 'status' => 1])->save();
        $city = ProvinceCity::query()->first();
        $this->assertNotNull($city);
        $user->profile()->updateOrCreate([], [
            'headline' => 'طراح تجربه کاربری',
            'province_city_id' => $city->id,
            'is_public' => true,
        ]);
        $uuid = InstructorPublicUuid::ensureFor($user);
        $slug = UserPublicSlug::forUser($user->fresh());

        Post::query()->create([
            'author_id' => $user->id,
            'title' => 'پست عمومی سارا',
            'slug' => 'sara-public-post',
            'excerpt' => 'خلاصه',
            'content' => 'متن',
            'status' => PostStatus::Published,
            'kind' => PostKind::Community,
            'published_at' => now()->subHour(),
        ]);

        $this->get('/u/'.$uuid)
            ->assertRedirect('/u/'.$uuid.'/'.$slug);

        $this->get('/u/'.$uuid.'/'.$slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Users/Show')
                ->where('profile.fullName', 'سارا محمدی')
                ->where('profile.headline', 'طراح تجربه کاربری')
                ->where('profile.uuid', $uuid)
                ->has('profile.posts', 1)
                ->where('profile.posts.0.title', 'پست عمومی سارا')
                ->where('seo.title', 'سارا محمدی · طراح تجربه کاربری'));
    }

    public function test_posting_requires_login_complete_profile_and_enrollment(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/account/posts/create')
            ->assertRedirect('/login');

        $user = User::where('mobile', '09120000003')->firstOrFail();
        $user->forceFill(['name' => null, 'family' => null])->save();

        $this->actingAs($user)
            ->get('/account/posts/create')
            ->assertRedirect(route('site.profile.edit'));

        $city = ProvinceCity::query()->first();
        $this->assertNotNull($city);

        $user->forceFill(['name' => 'سارا', 'family' => 'محمدی'])->save();
        $user->profile()->updateOrCreate([], ['province_city_id' => $city->id, 'is_public' => true]);

        $this->actingAs($user->fresh())
            ->get('/account/posts/create')
            ->assertNotFound();

        $this->confirmEnrollmentFor($user);

        $this->actingAs($user->fresh())
            ->get('/account/posts/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Account/Blog/Posts/Create'));
    }

    public function test_instructor_can_create_post_without_enrollment(): void
    {
        $this->seed(DatabaseSeeder::class);

        $instructor = User::where('mobile', '09120000002')->firstOrFail();
        $city = ProvinceCity::query()->firstOrFail();
        $instructor->forceFill(['name' => 'استاد', 'family' => 'تست'])->save();
        $instructor->profile()->updateOrCreate([], [
            'province_city_id' => $city->id,
            'is_public' => true,
        ]);

        $this->actingAs($instructor->fresh())
            ->get('/account/posts/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Account/Blog/Posts/Create'));
    }

    private function confirmEnrollmentFor(User $user): void
    {
        $course = Course::query()->create([
            'title' => 'دوره پست',
            'slug' => 'course-for-posting',
            'status' => CourseStatus::Published,
        ]);

        $class = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'گروه پست',
            'code' => 'POST-01',
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
}

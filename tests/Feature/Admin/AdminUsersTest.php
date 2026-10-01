<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Users\Support\UserAvatarMedia;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_access_users_index(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($student)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_super_admin_can_view_users_index(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Index')
                ->has('users.data', 3)
                ->where('abilities.create', true)
            );
    }

    public function test_super_admin_can_create_user(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'کاربر',
                'family' => 'تست',
                'mobile' => '09123334455',
                'email' => 'newuser@sharifi.test',
                'gender' => 'female',
                'status' => 1,
                'password' => 'password-12',
                'roles' => ['student'],
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'mobile' => '09123334455',
            'email' => 'newuser@sharifi.test',
        ]);

        $user = User::where('mobile', '09123334455')->firstOrFail();
        $this->assertTrue($user->hasRole('student'));
    }

    public function test_super_admin_can_upload_avatar_when_creating_user(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'با',
                'family' => 'آواتار',
                'mobile' => '09124445566',
                'email' => 'avatar@sharifi.test',
                'gender' => 'male',
                'status' => 1,
                'password' => 'password-12',
                'roles' => ['student'],
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200)->size(500),
                'avatar_alt' => 'تصویر کاربر',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('mobile', '09124445566')->firstOrFail();
        $this->assertNotNull($user->getFirstMedia(UserAvatarMedia::COLLECTION));
        $this->assertSame('تصویر کاربر', $user->getFirstMedia(UserAvatarMedia::COLLECTION)?->getCustomProperty('alt'));
    }

    public function test_avatar_must_not_exceed_two_megabytes(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'بزرگ',
                'family' => 'فایل',
                'mobile' => '09125556677',
                'status' => 1,
                'password' => 'password-12',
                'roles' => ['student'],
                'avatar' => UploadedFile::fake()->image('big.jpg')->size(3000),
            ])
            ->assertSessionHasErrors('avatar');
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->delete('/admin/users/'.$admin->id)
            ->assertForbidden();
    }
}

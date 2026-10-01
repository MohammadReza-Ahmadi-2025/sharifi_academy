<?php

namespace Tests\Feature\Admin;

use App\Modules\Roles\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_access_roles_index(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($student)
            ->get('/admin/roles')
            ->assertForbidden();
    }

    public function test_super_admin_can_view_roles_index(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Roles/Index')
                ->has('roles', 4)
                ->where('abilities.create', true)
            );
    }

    public function test_super_admin_can_create_custom_role_with_permissions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/roles', [
                'name' => 'content-editor',
                'permissions' => ['admin-dashboard-view'],
            ])
            ->assertRedirect(route('admin.roles.index'));

        $role = Role::query()->where('name', 'content-editor')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('admin-dashboard-view'));
    }

    public function test_cannot_delete_system_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();
        $role = Role::query()->where('name', 'student')->firstOrFail();

        $this->actingAs($admin)
            ->delete("/admin/roles/{$role->id}")
            ->assertForbidden();
    }
}

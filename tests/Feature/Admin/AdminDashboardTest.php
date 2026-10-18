<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_student_cannot_access_admin_panel(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('mobile', '09120000003')->firstOrFail();

        $this->actingAs($student)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_super_admin_can_view_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->has('statGroups', 4)
                ->has('charts.usersMonthly', 6)
                ->has('charts.rolesBreakdown', 3)
                ->has('charts.visitsDaily', 7)
                ->has('adminPanel.navigation')
                ->where('adminPanel.navigation.0.id', 'dashboard')
            );
    }
}

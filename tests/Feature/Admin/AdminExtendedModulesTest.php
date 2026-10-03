<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExtendedModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_new_admin_sections(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)->get('/admin/catalog/warehouses')->assertOk()->assertInertia(fn ($p) => $p->component('Admin/Catalog/Warehouses/Index'));
        $this->actingAs($admin)->get('/admin/catalog/inventory')->assertOk()->assertInertia(fn ($p) => $p->component('Admin/Catalog/Inventory/Index'));
        $this->actingAs($admin)->get('/admin/skills')->assertOk()->assertInertia(fn ($p) => $p->component('Admin/Skills/Index'));
        $this->actingAs($admin)->get('/admin/locations')->assertOk()->assertInertia(fn ($p) => $p->component('Admin/Locations/Index'));
        $this->actingAs($admin)->get('/admin/files')->assertOk()->assertInertia(fn ($p) => $p->component('Admin/Files/Index'));
        $this->actingAs($admin)->get('/admin/session-attendances')->assertOk()->assertInertia(fn ($p) => $p->component('Admin/SessionAttendances/Index'));
    }
}

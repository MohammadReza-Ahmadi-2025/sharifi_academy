<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use App\Modules\Catalog\Models\Brand;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogBrandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_brands_and_activity_is_logged(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/catalog/brands', [
                'title' => 'برند تست',
                'status' => true,
                'sort' => 1,
            ])
            ->assertRedirect();

        $brand = Brand::query()->where('title', 'برند تست')->first();
        $this->assertNotNull($brand);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'created',
            'subject_type' => Brand::class,
            'subject_id' => $brand->id,
        ]);

        $this->actingAs($admin)
            ->put("/admin/catalog/brands/{$brand->id}", [
                'title' => 'برند ویرایش‌شده',
                'status' => true,
                'sort' => 2,
            ])
            ->assertRedirect();

        $updatedLog = ActivityLog::query()
            ->where('subject_id', $brand->id)
            ->where('action', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($updatedLog);
        $this->assertArrayHasKey('changes', $updatedLog->properties);
        $this->assertArrayHasKey('title', $updatedLog->properties['changes']);

        $this->actingAs($admin)
            ->get('/admin/catalog/brands')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Catalog/Brands/Index'));

        $this->actingAs($admin)
            ->get("/admin/activity-logs/{$updatedLog->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/ActivityLogs/Show')
                ->has('log.changes')
            );

        $this->actingAs($admin)
            ->delete("/admin/catalog/brands/{$brand->id}")
            ->assertRedirect(route('admin.catalog.brands.index'));

        $this->assertSoftDeleted('brands', ['id' => $brand->id]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'deleted',
            'subject_id' => $brand->id,
        ]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Catalog\Models\Product;
use App\Modules\Visits\Models\Visit;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVisitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_visits_index(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('mobile', '09120000001')->firstOrFail();

        $product = Product::query()->first();
        if ($product) {
            Visit::query()->create([
                'visitable_type' => Product::class,
                'visitable_id' => $product->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/visits')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Visits/Index')
                ->has('stats', 3)
            );
    }
}

<?php

namespace Tests\Feature\Roles;

use App\Modules\Roles\Models\Permission;
use App\Modules\Roles\Support\PermissionCatalog;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionCatalogSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_config_permissions_exist_after_seeding(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (PermissionCatalog::allSlugs() as $slug) {
            $this->assertTrue(
                Permission::query()->where('name', $slug)->where('guard_name', 'web')->exists(),
                "Missing permission: {$slug}",
            );
        }
    }
}

<?php

use App\Modules\Roles\Models\Permission;
use App\Modules\Roles\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'admin-panel-access',
            'admin-dashboard-view',
        ];

        foreach ($permissions as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $roles = Role::query()->whereIn('name', ['super-admin', 'admin'])->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }
    }

    public function down(): void
    {
        Permission::query()
            ->whereIn('name', ['admin-panel-access', 'admin-dashboard-view'])
            ->where('guard_name', 'web')
            ->delete();
    }
};

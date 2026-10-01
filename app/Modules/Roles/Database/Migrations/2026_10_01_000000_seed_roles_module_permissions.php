<?php

use App\Modules\Roles\Models\Role;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DefaultRolePermissions::sync();
    }

    public function down(): void
    {
        // پرمیشن‌ها ممکن است به نقش‌های دیگر هم وصل باشند؛ فقط از admin/super-admin جدا می‌کنیم.
        $names = ['roles-index', 'roles-create', 'roles-update', 'roles-delete'];

        foreach (Role::query()->whereIn('name', ['super-admin', 'admin'])->get() as $role) {
            foreach ($names as $name) {
                $role->revokePermissionTo($name);
            }
        }
    }
};

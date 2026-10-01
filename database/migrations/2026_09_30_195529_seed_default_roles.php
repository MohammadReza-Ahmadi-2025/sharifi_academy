<?php

use App\Modules\Roles\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['super-admin', 'admin', 'instructor', 'student'] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }
    }

    public function down(): void
    {
        Role::query()
            ->whereIn('name', ['super-admin', 'admin', 'instructor', 'student'])
            ->where('guard_name', 'web')
            ->delete();
    }
};

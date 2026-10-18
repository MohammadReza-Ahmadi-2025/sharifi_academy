<?php

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
        // پرمیشن‌ها در کاتالوگ می‌مانند؛ sync مجدد نقش‌ها در down لازم نیست.
    }
};

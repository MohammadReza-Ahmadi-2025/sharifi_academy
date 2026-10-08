<?php

use App\Modules\Catalog\Models\Attribute;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Attribute::query()
            ->where('slug', '!=', 'color')
            ->update(['is_variant' => false]);

        Attribute::query()
            ->where('slug', 'color')
            ->update(['is_variant' => true]);
    }

    public function down(): void
    {
        // intentional no-op: prior is_variant flags varied by environment
    }
};

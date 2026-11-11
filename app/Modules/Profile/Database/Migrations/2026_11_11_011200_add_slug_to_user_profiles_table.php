<?php

use App\Models\User;
use App\Modules\Profile\Support\InstructorPublicUuid;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historical filename kept; column is public UUID (not slug).
        if (! Schema::hasColumn('user_profiles', 'uuid')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('user_id');
            });
        }

        User::query()
            ->whereHas('profile')
            ->with('profile')
            ->orderBy('id')
            ->each(function (User $user): void {
                InstructorPublicUuid::ensureFor($user);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_profiles', 'uuid')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            });
        }
    }
};

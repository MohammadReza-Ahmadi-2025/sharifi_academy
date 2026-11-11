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
        if (Schema::hasColumn('user_profiles', 'slug')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }

        if (! Schema::hasColumn('user_profiles', 'uuid')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('user_id');
            });
        }

        User::query()
            ->whereHas('profile', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->whereNull('uuid')->orWhere('uuid', '');
                });
            })
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

        if (! Schema::hasColumn('user_profiles', 'slug')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->string('slug', 120)->nullable()->unique()->after('user_id');
            });
        }
    }
};

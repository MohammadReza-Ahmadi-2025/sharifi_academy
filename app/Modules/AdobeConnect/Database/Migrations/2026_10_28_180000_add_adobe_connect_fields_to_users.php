<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('adobe_connect_principal_id', 32)->nullable()->after('mobile_verified_at');
            $table->timestamp('adobe_connect_synced_at')->nullable()->after('adobe_connect_principal_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['adobe_connect_principal_id', 'adobe_connect_synced_at']);
        });
    }
};

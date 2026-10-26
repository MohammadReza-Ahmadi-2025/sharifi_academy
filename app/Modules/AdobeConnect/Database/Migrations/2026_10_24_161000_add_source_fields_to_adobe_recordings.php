<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adobe_recordings', function (Blueprint $table) {
            $table->string('url_path', 500)->nullable()->after('name');
            $table->string('download_url', 1000)->nullable()->after('playback_url');
            $table->timestamp('recorded_at')->nullable()->after('duration_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('adobe_recordings', function (Blueprint $table) {
            $table->dropColumn(['url_path', 'download_url', 'recorded_at']);
        });
    }
};

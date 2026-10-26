<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->string('online_platform', 32)
                ->default('adobe_connect')
                ->after('delivery_type');
        });

        Schema::table('class_sessions', function (Blueprint $table) {
            $table->string('video_url', 500)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });

        Schema::table('course_classes', function (Blueprint $table) {
            $table->dropColumn('online_platform');
        });
    }
};

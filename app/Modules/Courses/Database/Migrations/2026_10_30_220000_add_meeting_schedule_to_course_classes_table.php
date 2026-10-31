<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->json('meeting_weekdays')->nullable()->after('ends_at');
            $table->time('meeting_starts_time')->nullable()->after('meeting_weekdays');
            $table->time('meeting_ends_time')->nullable()->after('meeting_starts_time');
        });
    }

    public function down(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            $table->dropColumn(['meeting_weekdays', 'meeting_starts_time', 'meeting_ends_time']);
        });
    }
};

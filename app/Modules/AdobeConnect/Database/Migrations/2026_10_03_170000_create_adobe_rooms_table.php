<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adobe_rooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_class_id')
                ->constrained('course_classes')
                ->cascadeOnDelete();

            $table->foreignId('class_session_id')
                ->nullable()
                ->constrained('class_sessions')
                ->nullOnDelete();

            $table->string('sco_id', 64)->unique();
            $table->string('url_path')->unique();
            $table->string('meeting_url');

            $table->string('access_type', 20)->default('protected');
            $table->string('passcode')->nullable();

            $table->string('status', 20)->default('active');

            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            $table->index(['course_class_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adobe_rooms');
    }
};

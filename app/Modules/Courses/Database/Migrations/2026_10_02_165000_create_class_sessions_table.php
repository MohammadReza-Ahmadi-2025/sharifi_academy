<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_class_id')
                ->constrained('course_classes')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('session_number');
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            $table->string('status', 20)
                ->default('scheduled')
                ->index();

            $table->timestamp('scheduled_start_at');
            $table->timestamp('scheduled_end_at');
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('actual_end_at')->nullable();

            $table->string('timezone', 64)
                ->default('Asia/Tehran');

            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['course_class_id', 'session_number'],
                'class_sessions_class_number_unique'
            );

            $table->index(
                ['course_class_id', 'status', 'scheduled_start_at'],
                'class_sessions_schedule_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};

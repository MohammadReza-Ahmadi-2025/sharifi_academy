<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_session_id')
                ->constrained('class_sessions')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('status', 20)
                ->default('absent')
                ->index();

            $table->string('role', 20)
                ->default('student');

            $table->timestamp('first_joined_at')->nullable();
            $table->timestamp('last_left_at')->nullable();

            $table->unsignedInteger('duration_minutes')->default(0);

            $table->string('source', 20)
                ->default('system');

            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique(['class_session_id', 'user_id'], 'session_user_unique');

            $table->index(['class_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_attendances');
    }
};

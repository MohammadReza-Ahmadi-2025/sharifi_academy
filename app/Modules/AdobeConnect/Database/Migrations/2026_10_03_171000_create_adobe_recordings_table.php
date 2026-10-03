<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adobe_recordings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_session_id')
                ->constrained('class_sessions')
                ->cascadeOnDelete();

            $table->string('sco_id', 64)->unique();
            $table->string('name')->nullable();
            $table->string('playback_url');

            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedBigInteger('size_bytes')->nullable();

            $table->boolean('is_public')->default(true);
            $table->boolean('is_passcode_protected')->default(false);
            $table->string('passcode')->nullable();

            $table->timestamps();

            $table->index(['class_session_id', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adobe_recordings');
    }
};

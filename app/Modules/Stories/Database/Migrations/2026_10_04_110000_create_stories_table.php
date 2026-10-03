<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('file_id')
                ->nullable()
                ->constrained('files')
                ->restrictOnDelete();

            $table->string('type', 20);
            $table->text('content')->nullable();
            $table->string('background_color', 20)->nullable();

            $table->string('status', 20)->default('draft');
            $table->string('visibility', 20)->default('authenticated');

            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['user_id', 'status', 'published_at'],
                'stories_user_status_published_index',
            );

            $table->index(
                ['status', 'visibility', 'expires_at'],
                'stories_active_index',
            );

            $table->index(
                ['file_id'],
                'stories_file_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};

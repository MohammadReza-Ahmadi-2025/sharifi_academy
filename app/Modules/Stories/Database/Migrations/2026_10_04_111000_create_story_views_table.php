<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('story_views', function (Blueprint $table) {
            $table->id();

            $table->foreignId('story_id')
                ->constrained('stories')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('first_viewed_at');
            $table->timestamp('last_viewed_at');
            $table->unsignedInteger('view_count')->default(1);

            $table->timestamps();

            $table->unique(
                ['story_id', 'user_id'],
                'story_views_story_user_unique',
            );

            $table->index(
                ['story_id', 'last_viewed_at'],
                'story_views_story_last_viewed_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_views');
    }
};

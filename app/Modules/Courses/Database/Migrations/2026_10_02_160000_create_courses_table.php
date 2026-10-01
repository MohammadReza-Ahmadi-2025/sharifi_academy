<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            $table->string('code', 50)
                ->nullable()
                ->unique();

            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->string('level', 30)
                ->nullable()
                ->index();

            $table->unsignedInteger('duration_minutes')->nullable();

            $table->string('status', 20)
                ->default('draft')
                ->index();

            $table->boolean('is_featured')
                ->default(false)
                ->index();

            $table->unsignedSmallInteger('display_order')->default(0);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

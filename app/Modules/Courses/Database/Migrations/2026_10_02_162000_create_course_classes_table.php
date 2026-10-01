<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_classes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code', 80)->unique();

            $table->string('status', 20)
                ->default('draft')
                ->index();

            $table->string('delivery_type', 20)
                ->default('online')
                ->index();

            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedBigInteger('price')->default(0);

            $table->timestamp('registration_starts_at')->nullable();
            $table->timestamp('registration_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->string('location')->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_public')->default(false)->index();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'status']);
            $table->index(['status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_classes');
    }
};

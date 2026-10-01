<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_instructors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_class_id')
                ->constrained('course_classes')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('role', 20)->default('lead');

            $table->boolean('is_primary')->default(false);

            $table->decimal('share_percentage', 5, 2)->nullable();

            $table->timestamps();

            $table->unique(['course_class_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_instructors');
    }
};

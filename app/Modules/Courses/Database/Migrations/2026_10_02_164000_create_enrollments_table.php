<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_class_id')
                ->constrained('course_classes')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('status', 20)
                ->default('pending');

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('reserved_until')->nullable();

            $table->unsignedBigInteger('price_amount')->default(0);

            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->unique(
                ['course_class_id', 'user_id'],
                'enrollments_class_user_unique'
            );

            $table->index(
                ['course_class_id', 'status'],
                'enrollments_class_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};

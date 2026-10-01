<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('headline')->nullable();
            $table->text('bio')->nullable();

            $table->date('birth_date')->nullable();
            $table->string('national_code', 10)->nullable()->unique();

            $table->foreignId('province_city_id')
                ->nullable()
                ->constrained('province_cities')
                ->nullOnDelete();

            $table->boolean('is_public')->default(true);
            $table->boolean('is_verified')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};

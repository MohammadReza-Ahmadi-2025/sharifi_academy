<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('province_cities', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('vista_id')
                ->nullable()
                ->comment('Vestano province/city ID used for receiver_province / receiver_city in external-order API')
                ->index();

            $table->foreignId('user_id')
                ->default(1)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('province_cities')
                ->cascadeOnDelete();

            $table->string('title', 100);

            $table->unsignedTinyInteger('sort')
                ->default(1);

            $table->softDeletes();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('province_cities');
    }
};

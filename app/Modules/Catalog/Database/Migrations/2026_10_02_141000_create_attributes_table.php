<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->enum('type', ['text', 'select', 'color', 'number', 'boolean'])
                ->default('select');

            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_visible_on_product')->default(true);
            $table->boolean('is_variant')->default(true);
            $table->boolean('is_active')->default(true);

            $table->integer('sort')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};

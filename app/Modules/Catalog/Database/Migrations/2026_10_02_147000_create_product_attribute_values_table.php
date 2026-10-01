<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('attribute_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('attribute_value_id')
                ->nullable()
                ->constrained('attribute_values')
                ->nullOnDelete();

            $table->string('value')->nullable();
            $table->boolean('is_highlight')->default(false);
            $table->integer('sort')->default(0);

            $table->timestamps();

            $table->unique(
                ['product_id', 'attribute_id', 'attribute_value_id'],
                'pav_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};

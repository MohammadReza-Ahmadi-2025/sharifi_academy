<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->decimal('price', 15, 2);
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('discount_value', 8, 2)->nullable();
            $table->decimal('final_price', 15, 2)->nullable();

            $table->timestamp('sale_start')->nullable();
            $table->timestamp('sale_end')->nullable();

            $table->string('currency', 3)->default('IRT');

            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->index(['variant_id', 'ends_at']);
            $table->index(['sale_start', 'sale_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};

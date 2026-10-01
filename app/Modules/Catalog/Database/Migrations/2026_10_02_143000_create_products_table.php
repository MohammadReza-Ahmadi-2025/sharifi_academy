<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique();

            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->longText('features')->nullable();

            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('model')->nullable();

            $table->boolean('status')->default(true);
            $table->boolean('is_visible')->default(true);

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('canonical')->nullable();
            $table->json('meta_keywords')->nullable();

            $table->integer('sort')->default(0);
            $table->unsignedBigInteger('sales_count')->default(0)->index();
            $table->unsignedBigInteger('views')->default(0)->index();

            $table->unsignedInteger('available_stock')->default(0)->index();
            $table->unsignedBigInteger('min_effective_price')->nullable()->index();
            $table->unsignedBigInteger('card_compare_price')->nullable();
            $table->string('card_display_mode', 30)->default('normal')->index();
            $table->unsignedTinyInteger('max_discount_percent')->default(0)->index();
            $table->boolean('card_discount_is_campaign')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

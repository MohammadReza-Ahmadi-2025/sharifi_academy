<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('external');
            $table->string('url')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('target_blank')->default(false);
            $table->timestamps();

            $table->index(['menu_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};

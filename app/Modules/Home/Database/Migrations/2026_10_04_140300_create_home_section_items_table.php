<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tab_id')->constrained('home_section_tabs')->cascadeOnDelete();
            $table->morphs('item');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tab_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_section_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_section_tabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('home_sections')->cascadeOnDelete();
            $table->string('title')->default('default');
            $table->string('content_type');
            $table->string('source_type');
            $table->unsignedInteger('limit')->default(8);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['section_id', 'sort_order']);
            $table->index(['content_type', 'source_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_section_tabs');
    }
};

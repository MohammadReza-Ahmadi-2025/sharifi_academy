<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->string('title')->default('')->after('user_id');

            $table->foreignId('thumbnail_file_id')
                ->nullable()
                ->after('file_id')
                ->constrained('files')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('thumbnail_file_id');
            $table->dropColumn('title');
        });
    }
};

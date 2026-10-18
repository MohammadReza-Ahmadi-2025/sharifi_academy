<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('resume_moderation_status', 20)->default('draft')->after('is_verified');
            $table->timestamp('resume_submitted_at')->nullable()->after('resume_moderation_status');
            $table->timestamp('resume_reviewed_at')->nullable()->after('resume_submitted_at');
            $table->foreignId('resume_reviewed_by')->nullable()->after('resume_reviewed_at')->constrained('users')->nullOnDelete();
            $table->text('resume_moderation_note')->nullable()->after('resume_reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resume_reviewed_by');
            $table->dropColumn([
                'resume_moderation_status',
                'resume_submitted_at',
                'resume_reviewed_at',
                'resume_moderation_note',
            ]);
        });
    }
};

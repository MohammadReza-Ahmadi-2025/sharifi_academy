<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_otps', function (Blueprint $table) {
            $table->id();

            $table->string('mobile', 15)->index();

            $table->string('type', 30)->default('login');

            $table->string('code_hash');

            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(5);

            $table->enum('status', [
                'pending',
                'verified',
                'expired',
                'blocked',
                'failed',
            ])->default('pending');

            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index(['mobile', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_otps');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('identifier', 100);

            $table->enum('channel', ['sms', 'email']);

            $table->string('otp_code', 10)->nullable();

            $table->enum('purpose', [
                'signup',
                'login',
                'login_resend',
                'forgot_password',
                'forgot_password_resend',
            ]);

            $table->enum('status', [
                'sent',
                'verified',
                'expired',
                'failed'
            ])->default('sent');

            $table->string('ip_address')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_logs');
    }
};

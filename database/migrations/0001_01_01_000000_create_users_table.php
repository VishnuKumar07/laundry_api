<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->enum('role', ['vendor','branch','customer'])
                  ->default('vendor');

            $table->string('first_name');
            $table->string('last_name');

            $table->string('primary_mobile', 20)->unique();
            $table->timestamp('primary_mobile_verified_at')->nullable();

            $table->string('otp_code', 10)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('otp_sent_at')->nullable();

            $table->string('secondary_mobile', 20)->nullable();

            $table->string('primary_email', 191)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('secondary_email')->nullable();

            $table->string('password');
            $table->string('sample_pass', 255);
            $table->rememberToken();

            $table->tinyInteger('status')->default(1);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                  ->constrained('vendors')
                  ->onDelete('cascade');

            $table->string('door_no')->nullable();
            $table->string('street')->nullable();
            $table->string('landmark')->nullable();

            $table->string('pincode', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->enum('address_type', ['shop','office','factory','others'])
                  ->default('office');

            $table->text('company_image')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_addresses');
    }
};

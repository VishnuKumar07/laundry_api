<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendor_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained()
                ->cascadeOnDelete();

            // Service
            $table->integer('service_radius')->nullable(); // km
            $table->json('service_pincodes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('avg_delivery_time')->nullable(); // days
            $table->integer('years_in_business')->nullable();

            // Payments
            $table->boolean('online_payment')->default(false);
            $table->boolean('cod')->default(false);

            // Delivery rules
            $table->boolean('pre_booking')->default(false);
            $table->integer('pre_booking_days')->nullable();

            $table->boolean('free_delivery')->default(false);
            $table->decimal('min_delivery_charge', 8, 2)->nullable();
            $table->decimal('min_cart_value', 8, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_settings');
    }
};

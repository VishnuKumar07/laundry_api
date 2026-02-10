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
        Schema::create('vendor_coupons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained()
                ->cascadeOnDelete();

            // Basic info
            $table->string('title');
            $table->string('code')->unique();

            // Date validity
            $table->date('from_date');
            $table->date('to_date');

            // Offer type
            $table->enum('offer_type', ['percentage', 'amount']);

            // Offer values
            $table->decimal('offer_value', 8, 2);
            $table->decimal('max_discount_amount', 8, 2)->nullable();
            $table->decimal('min_item_value', 8, 2)->nullable();

            // Usage limits
            $table->integer('coupon_limit_per_user')->nullable();
            $table->integer('coupon_limit_overall')->nullable();

            // Extra
            $table->text('description')->nullable();
            $table->json('images')->nullable();

            // Status
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_coupons');
    }
};

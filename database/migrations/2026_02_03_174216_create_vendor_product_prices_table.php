<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_product_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_product_id')
                  ->constrained('vendor_products')
                  ->cascadeOnDelete();

            $table->foreignId('service_id')
                  ->constrained('services')
                  ->cascadeOnDelete();

            $table->foreignId('delivery_type_id')
                  ->constrained('delivery_types')
                  ->cascadeOnDelete();

            $table->decimal('mrp', 8, 2);
            $table->decimal('selling_price', 8, 2);
            $table->decimal('cgst', 5, 2)->default(0);
            $table->decimal('sgst', 5, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['vendor_product_id', 'service_id', 'delivery_type_id'],
                'vendor_product_service_delivery_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_product_prices');
    }
};

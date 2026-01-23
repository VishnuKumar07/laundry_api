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
        Schema::create('vendor_service_type', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete();

            $table->foreignId('delivery_type_id')
                ->constrained('delivery_types')
                ->cascadeOnDelete();

            // business data
            $table->unsignedInteger('avg_delivery_days');

            $table->timestamps();
            $table->softDeletes();

            // prevent duplicate combinations
            $table->unique([
                'vendor_id',
                'service_id',
                'delivery_type_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_service_type');
    }
};

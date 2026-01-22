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
        Schema::create('vendor_working_hours', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            $table->enum('day', [
                'monday', 'tuesday', 'wednesday',
                'thursday', 'friday', 'saturday', 'sunday'
            ]);

            $table->boolean('is_open')->default(true);
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->unique(['vendor_id', 'day']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_working_hours');
    }
};

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
        Schema::create('vendor_terms_conditions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->longText('content'); 

            $table->timestamps();
            $table->softDeletes();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_terms_conditions');
    }
};

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
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('center_latitude', 10, 8);
            $table->decimal('center_longitude', 11, 8);
            $table->decimal('radius', 8, 2); // Radio en kilómetros
            $table->decimal('delivery_fee', 8, 2)->default(0.00);
            $table->integer('delivery_time')->default(30); // Tiempo estimado en minutos
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            // Índices para búsquedas eficientes
            $table->index('is_active');
            $table->index(['center_latitude', 'center_longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla order_delivery con campos en inglés desde el inicio.
     * Consolidado: rename_estado_envio_to_status, alter_status_column, y renombrado de campos en español.
     */
    public function up(): void
    {
        Schema::create('order_delivery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('delivery_agents')->onDelete('cascade');
            $table->string('status', 32); // Consolidado: directamente 'status' con tamaño 32
            $table->decimal('delivery_fee', 10, 2); // En inglés desde el inicio (antes 'costo_envio')
            $table->text('notes')->nullable(); // En inglés desde el inicio (antes 'notas')
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery');
    }
};

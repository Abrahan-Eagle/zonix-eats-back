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
        Schema::create('stations', function (Blueprint $table) {
            $table->id(); // Identificador único
            $table->string('name', 191); // Nombre de la estación
            $table->string('location', 255)->nullable(); // Dirección (opcional)
            $table->string('code_plus', 255)->nullable(); // Dirección (code_plus)
            $table->decimal('latitude', 10, 8)->nullable(); // Latitud GPS
            $table->decimal('longitude', 11, 8)->nullable(); // Longitud GPS
            $table->string('contact_number', 15)->nullable(); // Número de contacto
            $table->string('responsible_person', 191)->nullable(); // Persona responsable
            $table->string('days_available', 191); // Días disponibles (ejemplo: "Monday,Tuesday")
            $table->time('opening_time')->default('09:00:00'); // Hora de apertura
            $table->time('closing_time')->default('17:00:00'); // Hora de cierre
            $table->boolean('active')->default(true); // Estado activo/inactivo
            $table->string('code', 50)->unique(); // Código único de la estación
            $table->timestamps(); // Campos created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};

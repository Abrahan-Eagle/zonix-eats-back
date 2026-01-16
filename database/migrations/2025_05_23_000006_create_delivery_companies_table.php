<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla delivery_companies con nombres en inglés desde el inicio.
     * Consolidado: rename_spanish_fields_to_english (campos ya en inglés).
     */
    public function up(): void
    {
        Schema::create('delivery_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->string('name'); // En inglés desde el inicio
            $table->string('tax_id')->unique(); // En inglés desde el inicio (antes 'ci')
            $table->string('phone'); // En inglés desde el inicio (antes 'telefono')
            $table->text('address'); // En inglés desde el inicio (antes 'direccion')
            $table->boolean('active')->default(true); // En inglés desde el inicio (antes 'activo')
            // Campos agregados después
            $table->text('image')->nullable()->comment('Logo de la empresa de delivery');
            $table->boolean('open')->default(false)->comment('Si la empresa está abierta/disponible');
            $table->json('schedule')->nullable()->comment('Horario de atención de la empresa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_companies');
    }
};

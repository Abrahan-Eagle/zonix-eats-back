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
        Schema::create('gas_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la empresa proveedora de gas
            $table->string('contact_info')->nullable(); // Información de la empresa proveedora de gas
            $table->string('address')->nullable(); // direccion de la empresa proveedora de gas
            $table->boolean('status')->default(false); //status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gas_suppliers');
    }
};

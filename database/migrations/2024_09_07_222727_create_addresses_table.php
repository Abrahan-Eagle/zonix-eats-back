<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla addresses con todos los campos consolidados de migraciones "add".
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('street');
            $table->string('house_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7); // Almacena la latitud con 7 decimales de precisión
            $table->decimal('longitude', 10, 7); // Almacena la longitud con 7 decimales de precisión
            $table->enum('status', ['completeData', 'incompleteData', 'notverified'])->default('notverified');
            $table->boolean('is_default')->default(false); // Marca dirección predeterminada (casa)
            $table->timestamps();

            // Claves foráneas
            $table->unsignedBigInteger('profile_id');
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->unsignedBigInteger('city_id');  // Relación con ciudades
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla delivery_agents con todos los campos consolidados y nombres en inglés.
     * Consolidado: 
     * - rename_spanish_fields_to_english (campos ya en inglés: status, working)
     * - make_company_id_nullable (company_id nullable desde el inicio)
     * - make_vehicle_type_and_phone_nullable (ya nullable desde el inicio)
     * - add_location_fields (current_latitude, current_longitude, last_location_update)
     * - add_rejection_tracking (rejection_count, last_rejection_date)
     */
    public function up(): void
    {
        Schema::create('delivery_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('delivery_companies')->onDelete('cascade'); // Nullable para independientes
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['activo', 'inactivo', 'suspendido'])->default('activo'); // En inglés desde el inicio
            $table->boolean('working')->default(false); // En inglés desde el inicio
            $table->decimal('rating', 3, 2)->nullable();
            $table->string('vehicle_type', 100)->nullable(); // Nullable desde el inicio
            $table->string('phone', 20)->nullable(); // Nullable desde el inicio
            $table->string('license_number')->nullable();
            // Campos de ubicación
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->timestamp('last_location_update')->nullable();
            // Campos de tracking de rechazos
            $table->integer('rejection_count')->default(0);
            $table->timestamp('last_rejection_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_agents');
    }
};

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
        // Si la tabla ya existe (por ejecuciones previas / consolidación de migraciones),
        // no intentar recrearla para evitar errores en migrate:refresh.
        if (Schema::hasTable('phones')) {
            return;
        }

        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
            // Contexto de uso y entidades asociadas (consolidados aquí, no en migraciones "add_*")
            $table->string('context', 32)->default('personal');
            $table->unsignedBigInteger('commerce_id')->nullable();
            $table->unsignedBigInteger('delivery_company_id')->nullable();

            $table->foreignId('operator_code_id')->constrained('operator_codes')->onDelete('cascade');
            $table->string('number', 7);
            $table->boolean('is_primary')->default(false);
            $table->boolean('status')->default(true);
            $table->boolean('approved')->default(false);
            $table->timestamps();

            // Índices para filtrar por contexto y entidad
            $table->index(['profile_id', 'context']);
            $table->index('commerce_id');
            $table->index('delivery_company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};

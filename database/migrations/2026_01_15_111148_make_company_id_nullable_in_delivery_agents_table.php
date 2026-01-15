<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Hace company_id nullable para permitir motorizados independientes
     * que no pertenecen a ninguna empresa de delivery.
     */
    public function up(): void
    {
        Schema::table('delivery_agents', function (Blueprint $table) {
            // Primero eliminar la foreign key constraint
            $table->dropForeign(['company_id']);
            
            // Hacer la columna nullable
            $table->foreignId('company_id')->nullable()->change();
            
            // Recrear la foreign key pero permitiendo null
            $table->foreign('company_id')
                ->references('id')
                ->on('delivery_companies')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_agents', function (Blueprint $table) {
            // Eliminar foreign key
            $table->dropForeign(['company_id']);
            
            // Hacer la columna required nuevamente
            $table->foreignId('company_id')->nullable(false)->change();
            
            // Recrear la foreign key
            $table->foreign('company_id')
                ->references('id')
                ->on('delivery_companies')
                ->onDelete('cascade');
        });
    }
};

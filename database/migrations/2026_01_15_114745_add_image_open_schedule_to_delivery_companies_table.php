<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega campos image (logo), open y schedule a delivery_companies
     * para tener la misma estructura que commerces
     */
    public function up(): void
    {
        Schema::table('delivery_companies', function (Blueprint $table) {
            // Verificar si existe 'direccion' (español) o 'address' (inglés)
            $hasAddress = Schema::hasColumn('delivery_companies', 'address');
            $afterColumn = $hasAddress ? 'address' : 'direccion';
            
            $table->text('image')->nullable()->after($afterColumn)->comment('Logo de la empresa de delivery');
            $table->boolean('open')->default(false)->after('image')->comment('Si la empresa está abierta/disponible');
            $table->json('schedule')->nullable()->after('open')->comment('Horario de atención de la empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_companies', function (Blueprint $table) {
            $table->dropColumn(['image', 'open', 'schedule']);
        });
    }
};

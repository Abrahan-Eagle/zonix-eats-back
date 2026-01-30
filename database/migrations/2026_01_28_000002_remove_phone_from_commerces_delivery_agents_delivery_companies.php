<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * El teléfono de comercio, delivery agent y delivery company se obtiene
     * del perfil (tabla phones). Una sola fuente de verdad para todos los usuarios.
     */
    public function up(): void
    {
        Schema::table('commerces', function (Blueprint $table) {
            $table->dropColumn('phone');
        });

        Schema::table('delivery_agents', function (Blueprint $table) {
            $table->dropColumn('phone');
        });

        Schema::table('delivery_companies', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commerces', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('image');
        });

        Schema::table('delivery_agents', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('license_number');
        });

        Schema::table('delivery_companies', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('tax_id');
        });
    }
};

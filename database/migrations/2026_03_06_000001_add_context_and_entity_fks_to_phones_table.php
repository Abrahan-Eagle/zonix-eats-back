<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade contexto de uso (personal, commerce, delivery_company, admin)
     * y FKs opcionales a commerce_id y delivery_company_id.
     * Un dueño puede tener muchos comercios; cada comercio puede tener varios teléfonos.
     */
    public function up(): void
    {
        Schema::table('phones', function (Blueprint $table) {
            $table->string('context', 32)->default('personal')->after('profile_id');
            $table->unsignedBigInteger('commerce_id')->nullable()->after('context');
            $table->unsignedBigInteger('delivery_company_id')->nullable()->after('commerce_id');

            $table->foreign('commerce_id')->references('id')->on('commerces')->onDelete('cascade');
            $table->foreign('delivery_company_id')->references('id')->on('delivery_companies')->onDelete('cascade');
        });

        // Índices para filtrar por contexto y entidad
        Schema::table('phones', function (Blueprint $table) {
            $table->index(['profile_id', 'context']);
            $table->index(['commerce_id']);
            $table->index(['delivery_company_id']);
        });
    }

    /**
     * Reverse the migrations.
     * SQLite no soporta dropForeign; en ese caso solo se eliminan las columnas.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('phones', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                $table->dropForeign(['commerce_id']);
                $table->dropForeign(['delivery_company_id']);
                $table->dropIndex(['profile_id', 'context']);
                $table->dropIndex(['commerce_id']);
                $table->dropIndex(['delivery_company_id']);
            }
            $table->dropColumn(['context', 'commerce_id', 'delivery_company_id']);
        });
    }
};

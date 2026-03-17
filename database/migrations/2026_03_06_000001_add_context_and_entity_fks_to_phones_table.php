<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo se asegura la creación de las FKs, ya que las columnas
        // `context`, `commerce_id` y `delivery_company_id` se definieron
        // en la migración create_phones_table.
        Schema::table('phones', function (Blueprint $table) {
            $table->foreign('commerce_id')
                ->references('id')
                ->on('commerces')
                ->onDelete('cascade');
            $table->foreign('delivery_company_id')
                ->references('id')
                ->on('delivery_companies')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // NO-OP: el rollback real de la tabla `phones` se hace
        // desde la migración `create_phones_table`.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Limpia la tabla documents: solo útil para la app (CI y RIF).
     * - Elimina columnas no usadas.
     * - Restringe type a solo 'ci' y 'rif'.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'RECEIPT_N',
                'sky',
                'rif_url',
                'commune_register',
                'community_rif',
            ]);
        });

        // Restringir tipo a solo CI y RIF (MySQL). Filas con otro tipo se dejan en NULL.
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::table('documents')->whereNotIn('type', ['ci', 'rif'])->update(['type' => null]);
            DB::statement("ALTER TABLE documents MODIFY type ENUM('ci','rif') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE documents MODIFY type ENUM('ci','passport','rif','neighborhood_association') NULL");
        }

        Schema::table('documents', function (Blueprint $table) {
            $table->bigInteger('RECEIPT_N')->nullable()->after('rif_number');
            $table->bigInteger('sky')->nullable()->after('RECEIPT_N');
            $table->string('rif_url')->nullable()->after('sky');
            $table->string('commune_register')->nullable()->after('taxDomicile');
            $table->string('community_rif')->nullable()->after('commune_register');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * RIF Venezuela: formato X-NNNNNNNN-N (ej. J-12345678-9, V-12345678-9).
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('rif_number', 20)->nullable()->after('community_rif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('rif_number');
        });
    }
};

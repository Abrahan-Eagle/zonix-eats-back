<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade payout_percentage (agentes) y default_payout_percentage (empresas).
     * Para nuevas instalaciones ya están en las migraciones create; esta migración aplica a BD existentes.
     */
    public function up(): void
    {
        if (Schema::hasTable('delivery_agents') && ! Schema::hasColumn('delivery_agents', 'payout_percentage')) {
            Schema::table('delivery_agents', function (Blueprint $table) {
                $table->decimal('payout_percentage', 5, 2)->default(70.00)->after('last_rejection_date');
            });
        }
        if (Schema::hasTable('delivery_companies') && ! Schema::hasColumn('delivery_companies', 'default_payout_percentage')) {
            Schema::table('delivery_companies', function (Blueprint $table) {
                $table->decimal('default_payout_percentage', 5, 2)->default(70.00)->after('schedule');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('delivery_agents', 'payout_percentage')) {
            Schema::table('delivery_agents', function (Blueprint $table) {
                $table->dropColumn('payout_percentage');
            });
        }
        if (Schema::hasColumn('delivery_companies', 'default_payout_percentage')) {
            Schema::table('delivery_companies', function (Blueprint $table) {
                $table->dropColumn('default_payout_percentage');
            });
        }
    }
};

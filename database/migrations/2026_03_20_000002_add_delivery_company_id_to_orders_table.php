<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade delivery_company_id a orders para vincular la orden a la empresa que asigna el repartidor.
     * (La migración create_orders corre antes que create_delivery_companies, por eso se añade aquí.)
     */
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'delivery_company_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('delivery_company_id')->nullable()->after('commerce_id')
                    ->constrained('delivery_companies')->onDelete('set null');
                $table->index(['delivery_company_id', 'status'], 'orders_delivery_company_status_index');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'delivery_company_id')) {
            return;
        }
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return; // SQLite doesn't support dropping foreign keys
        }
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_company_id']);
            $table->dropIndex('orders_delivery_company_status_index');
        });
    }
};

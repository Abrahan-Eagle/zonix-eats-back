<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'approved_for_payment')) {
                $table->boolean('approved_for_payment')
                    ->default(false)
                    ->after('status');
            }
        });

        // Marcar como aprobadas para pago las órdenes que ya tienen comprobante
        try {
            DB::table('orders')
                ->whereNotNull('payment_proof')
                ->update(['approved_for_payment' => true]);
        } catch (\Throwable $e) {
            // En caso de error en entornos sin tabla/orders, no romper migración
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'approved_for_payment')) {
                $table->dropColumn('approved_for_payment');
            }
        });
    }
};


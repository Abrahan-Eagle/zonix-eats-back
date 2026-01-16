<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Actualiza los estados de orders de los antiguos (preparing, on_way) 
     * a los nuevos (processing, shipped) según el modelo de negocio.
     */
    public function up(): void
    {
        // Primero actualizar los datos existentes
        DB::table('orders')
            ->where('status', 'preparing')
            ->update(['status' => 'processing']);
            
        DB::table('orders')
            ->where('status', 'on_way')
            ->update(['status' => 'shipped']);

        // Luego modificar el enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los datos
        DB::table('orders')
            ->where('status', 'processing')
            ->update(['status' => 'preparing']);
            
        DB::table('orders')
            ->where('status', 'shipped')
            ->update(['status' => 'on_way']);

        // Revertir el enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending_payment', 'paid', 'preparing', 'on_way', 'delivered', 'cancelled') NOT NULL");
    }
};

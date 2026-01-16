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
     * 
     * Nota: Si la tabla orders ya se creó con los estados nuevos (en create_orders_table),
     * esta migración solo actualizará datos existentes con estados antiguos.
     */
    public function up(): void
    {
        // Verificar si la tabla existe
        if (!Schema::hasTable('orders')) {
            return;
        }
        
        // Verificar si hay datos con estados antiguos antes de actualizar
        $hasPreparing = DB::table('orders')->where('status', 'preparing')->exists();
        $hasOnWay = DB::table('orders')->where('status', 'on_way')->exists();
        
        // Solo actualizar si hay datos con estados antiguos
        if ($hasPreparing) {
            DB::table('orders')
                ->where('status', 'preparing')
                ->update(['status' => 'processing']);
        }
            
        if ($hasOnWay) {
            DB::table('orders')
                ->where('status', 'on_way')
                ->update(['status' => 'shipped']);
        }

        // Modificar el enum solo si es necesario (verificar si el enum actual tiene los valores antiguos)
        try {
            // Intentar obtener información del enum actual
            $enumValues = DB::select("SHOW COLUMNS FROM orders WHERE Field = 'status'");
            if (!empty($enumValues)) {
                $enumString = $enumValues[0]->Type;
                // Si el enum contiene 'preparing' o 'on_way', actualizar
                if (strpos($enumString, 'preparing') !== false || strpos($enumString, 'on_way') !== false) {
                    DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL");
                }
            }
        } catch (\Exception $e) {
            // Si hay error, asumir que el enum ya está actualizado
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Nota: Durante migrate:refresh, las tablas se eliminan primero,
     * por lo que este método no se ejecutará. Si se necesita revertir manualmente,
     * se debe hacer con cuidado ya que la tabla orders ya se crea con los estados nuevos.
     */
    public function down(): void
    {
        // No hacer nada durante migrate:refresh ya que las tablas se eliminan primero
        // Si se necesita revertir manualmente, se debe hacer con cuidado
        // ya que la tabla orders ya se crea con los estados nuevos en create_orders_table
    }
};

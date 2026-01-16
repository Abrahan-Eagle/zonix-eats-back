<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla orders con todos los campos consolidados de migraciones "add".
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('commerce_id')->constrained()->onDelete('cascade');
            $table->enum('delivery_type', ['pickup', 'delivery']);
            $table->enum('status', ['pending_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled']); // Estados actualizados
            $table->decimal('total', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0); // Costo de delivery que paga el cliente
            $table->decimal('delivery_payment_amount', 10, 2)->nullable(); // Cantidad que recibe delivery (100% del delivery_fee)
            $table->decimal('commission_amount', 10, 2)->default(0); // Comisión de esta orden
            $table->decimal('cancellation_penalty', 10, 2)->default(0); // Penalización si cancela después de paid
            $table->string('cancelled_by')->nullable(); // user_id, commerce_id, admin_id
            $table->integer('estimated_delivery_time')->nullable(); // minutos, máx 60
            $table->text('receipt_url')->nullable();
            $table->string('payment_proof')->nullable(); // URL del comprobante de pago
            $table->string('payment_method')->nullable(); // Método de pago elegido
            $table->string('reference_number')->nullable(); // Número de referencia del pago
            $table->timestamp('payment_validated_at')->nullable(); // Cuándo se validó el pago
            $table->timestamp('payment_proof_uploaded_at')->nullable(); // Cuándo se subió comprobante
            $table->text('cancellation_reason')->nullable(); // Razón de cancelación
            $table->text('delivery_address')->nullable(); // Dirección de entrega
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices de performance (consolidados desde add_performance_indexes)
            $table->index('status', 'orders_status_index');
            $table->index('created_at', 'orders_created_at_index');
            $table->index('profile_id', 'orders_profile_id_index');
            $table->index('commerce_id', 'orders_commerce_id_index');
            $table->index(['commerce_id', 'status', 'created_at'], 'orders_commerce_status_created_index');
            $table->index(['profile_id', 'created_at'], 'orders_profile_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

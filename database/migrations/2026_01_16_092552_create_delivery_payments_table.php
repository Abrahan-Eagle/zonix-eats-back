<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla delivery_payments para trackear pagos a delivery según modelo de negocio.
     * El delivery recibe 100% del delivery_fee que pagó el cliente.
     */
    public function up(): void
    {
        Schema::create('delivery_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_agent_id')->constrained('delivery_agents')->onDelete('cascade');
            $table->decimal('amount', 10, 2); // 100% del delivery_fee
            $table->enum('status', ['pending_payment_to_delivery', 'paid_to_delivery'])->default('pending_payment_to_delivery');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('order_id');
            $table->index('delivery_agent_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_payments');
    }
};

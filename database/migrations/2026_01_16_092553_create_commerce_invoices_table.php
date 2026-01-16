<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla commerce_invoices para trackear facturas mensuales según modelo de negocio.
     * Cada factura incluye: membresía mensual + comisiones del mes.
     */
    public function up(): void
    {
        Schema::create('commerce_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_id')->constrained()->onDelete('cascade');
            $table->decimal('membership_fee', 10, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2); // membership_fee + commission_amount
            $table->date('invoice_date');
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('commerce_id');
            $table->index('status');
            $table->index('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commerce_invoices');
    }
};

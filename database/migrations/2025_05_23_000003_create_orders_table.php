<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('commerce_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Nueva columna
            $table->enum('tipo_entrega', ['pickup', 'delivery']);
            $table->enum('estado', ['pendiente_pago', 'pagado', 'preparando', 'en_camino', 'entregado', 'cancelado']);
            $table->decimal('total', 10, 2);
            $table->text('comprobante_url')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
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

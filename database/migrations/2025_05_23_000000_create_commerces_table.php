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
        Schema::create('commerces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->string('nombre_local');
            $table->text('imagen')->nullable();
            $table->text('direccion');
            $table->string('telefono', 20);
            $table->string('pago_movil_banco', 50);
            $table->string('pago_movil_cedula', 20);
            $table->string('pago_movil_telefono', 20);
            $table->boolean('abierto')->default(false);
            $table->json('horario')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commerces');
    }
};

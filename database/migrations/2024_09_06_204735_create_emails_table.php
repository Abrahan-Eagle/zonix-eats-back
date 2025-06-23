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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
            $table->string('email')->unique(); // Correo electrónico único
            $table->boolean('is_primary')->default(false); // Solo uno puede ser primario por perfil
            $table->boolean('status')->default(true); // se muestra el correo solo si esta activo
            $table->boolean('approved')->default(false);// significa si el documento esta aprovado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};

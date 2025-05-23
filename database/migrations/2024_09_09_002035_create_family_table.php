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
        Schema::create('family', function (Blueprint $table) {
            $table->id(); // Identificador único
            $table->unsignedBigInteger('profile_id'); // Relación con el usuario principal (perfil)
            // $table->unsignedBigInteger('related_profile_id')->nullable(); // Relación con el perfil relacionado (otro miembro de la familia)

            $table->string('firstName');
            $table->string('lastName');
            $table->string('ci');
            $table->string('email');
            // Parentesco (ej. padre, madre, hijo, esposa, etc.)
            $table->enum('relationship', ['father', 'mother', 'spouse', 'son', 'daughter', 'other'])->default('other');

            // Si es líder de familia (ej. el responsable del grupo familiar)
            $table->boolean('is_family_leader')->default(false);

            $table->timestamps(); // Fechas de creación y actualización

            // Relaciones de claves foráneas
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            // $table->foreign('related_profile_id')->references('id')->on('profiles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family');
    }
};

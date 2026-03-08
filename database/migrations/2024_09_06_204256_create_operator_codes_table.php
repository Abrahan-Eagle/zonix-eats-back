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
        Schema::create('operator_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('code')->unique(); // 412, 414, 424, 416, 426
            $table->string('name'); // Nombre del operador para mostrar, ej: "0412"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_codes');
    }
};

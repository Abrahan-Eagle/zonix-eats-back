<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla posts con nombres en inglés desde el inicio.
     * Consolidado: rename_spanish_fields_to_english (description en lugar de descripcion).
     */
    public function up(): void
    {
       Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_id')->constrained()->onDelete('cascade');
            $table->string('tipo');
            $table->string('media_url')->nullable();
            $table->text('description')->nullable(); // En inglés desde el inicio (antes 'descripcion')
            $table->string('name');
            $table->decimal('price', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

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
        Schema::create('gas_cylinders', function (Blueprint $table) {
            $table->id();
            $table->string('gas_cylinder_code')->unique(); //codigo de la bombona de gas
            $table->integer('cylinder_quantity')->nullable(); // Cantidad de bombonas
            $table->enum('cylinder_type', ['small', 'wide'])->nullable(); // tipo_boquilla (boca pequeña o ancha)
            $table->enum('cylinder_weight', ['10kg', '18kg', '45kg'])->nullable(); // Peso de la bombona (10kg, 18kg, 45kg)
            // $table->string('company');
            $table->boolean('approved')->default(false); //aprovacion de la bombona
            $table->string('photo_gas_cylinder')->nullable(); //foto_bombona de gas
            $table->date('manufacturing_date')->nullable(); //fecha_fabricacion
            $table->timestamps();


            // Relación con la tabla compania de gas
            $table->unsignedBigInteger('profile_id'); // Clave foránea hacia la tabla users
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->unsignedBigInteger('company_supplier_id')->nullable(); // nombre de la compania
            $table->foreign('company_supplier_id')->references('id')->on('gas_suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gas_cylinders');
    }
};

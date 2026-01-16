<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla products con todos los campos consolidados de migraciones "add".
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('category_id')->nullable(); // Foreign key a categories
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->text('image')->nullable();
            $table->boolean('available')->default(true);
            $table->integer('stock_quantity')->nullable()->comment('Cantidad en stock. Si es null, solo se usa available');
            $table->timestamps();
            
            // Foreign key para category_id
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

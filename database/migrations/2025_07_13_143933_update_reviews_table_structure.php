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
        Schema::table('reviews', function (Blueprint $table) {
            // Agregar nuevas columnas
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('commerce_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('delivery_agent_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['restaurant', 'delivery_agent'])->nullable();
            $table->json('photos')->nullable();
            
            // Renombrar comentario a comment
            $table->renameColumn('comentario', 'comment');
        });

        // Eliminar columnas morph
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['reviewable_id', 'reviewable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Restaurar columnas morph
            $table->morphs('reviewable');
            
            // Eliminar nuevas columnas
            $table->dropForeign(['order_id']);
            $table->dropForeign(['commerce_id']);
            $table->dropForeign(['delivery_agent_id']);
            $table->dropColumn(['order_id', 'commerce_id', 'delivery_agent_id', 'type', 'photos']);
            
            // Restaurar nombre de comentario
            $table->renameColumn('comment', 'comentario');
        });
    }
};

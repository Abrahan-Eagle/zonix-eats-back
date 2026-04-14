<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea tabla reviews con campo 'comment' directamente (no 'comentario').
     * Agregado: order_id para validar que se califica después de orden entregada.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade')->comment('Orden relacionada - Para validar que se califica después de orden entregada');
            $table->morphs('reviewable'); // reviewable_type, reviewable_id (commerce, delivery_agent)
            $table->tinyInteger('rating')->unsigned();
            $table->text('comment')->nullable(); // En inglés desde el inicio (antes 'comentario')
            $table->string('moderation_status', 16)->default('approved');
            $table->timestamp('reported_at')->nullable();
            $table->text('reported_reason')->nullable();
            $table->unsignedBigInteger('reported_by_profile_id')->nullable();
            $table->timestamps();

            $table->unique(['profile_id', 'order_id', 'reviewable_type', 'reviewable_id'], 'reviews_unique_profile_order_target');
            $table->index(['reviewable_type', 'reviewable_id', 'created_at'], 'reviews_target_created_at_index');
            $table->index(['moderation_status', 'reported_at'], 'reviews_moderation_status_reported_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea tabla disputes para sistema de quejas/tickets según modelo de negocio.
     * Permite a usuarios, comercios y delivery crear quejas sobre órdenes.
     */
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            // Quién reporta (puede ser user, commerce o delivery)
            $table->morphs('reported_by'); // reported_by_type, reported_by_id

            // Contra quién se reporta (puede ser user, commerce o delivery)
            $table->morphs('reported_against'); // reported_against_type, reported_against_id

            $table->enum('type', ['quality_issue', 'delivery_problem', 'payment_issue', 'other'])->default('other');
            $table->text('description');
            $table->enum('status', ['pending', 'in_review', 'resolved', 'closed'])->default('pending');
            $table->enum('resolution', ['refund', 'penalty', 'warning', 'closed'])->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('resolution_metadata')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Índices
            $table->index('order_id');
            $table->index('status');
            $table->index(['status', 'type', 'created_at'], 'disputes_status_type_created_at_idx');
            $table->index(['reported_by_type', 'reported_by_id', 'status', 'created_at'], 'disputes_reported_by_status_created_at_idx');
            // Nota: morphs() ya crea índices automáticamente para reported_by y reported_against
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};

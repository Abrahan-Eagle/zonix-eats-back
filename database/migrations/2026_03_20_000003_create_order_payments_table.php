<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['food', 'delivery']);
            $table->decimal('amount', 10, 2);
            $table->string('payee_type', 50)->nullable(); // commerce | delivery_company
            $table->unsignedBigInteger('payee_id')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->string('payment_method_label', 100)->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->string('payment_proof')->nullable();
            $table->timestamp('payment_proof_uploaded_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'type']);
            $table->index(['payee_type', 'payee_id', 'validated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};

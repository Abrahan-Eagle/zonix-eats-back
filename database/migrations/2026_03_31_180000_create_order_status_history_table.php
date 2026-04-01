<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('from_status', 32);
            $table->string('to_status', 32);
            $table->string('actor_role', 32);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('source', 64)->default('api');
            $table->string('reason', 255)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['order_id', 'occurred_at'], 'order_status_history_order_occurred_idx');
            $table->index(['to_status', 'occurred_at'], 'order_status_history_to_status_occurred_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id');
            $table->string('idempotency_key', 120);
            $table->string('request_fingerprint', 64);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedSmallInteger('status_code')->default(0);
            $table->timestamps();

            $table->unique(['profile_id', 'idempotency_key'], 'order_idempotency_profile_key_unique');
            $table->index(['profile_id', 'created_at'], 'order_idempotency_profile_created_index');
            $table->index('order_id', 'order_idempotency_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_idempotency_keys');
    }
};

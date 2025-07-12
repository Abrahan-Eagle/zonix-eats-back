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
        Schema::create('commerces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->text('image')->nullable();
            $table->text('address');
            $table->string('phone', 20);
            $table->string('mobile_payment_bank', 50);
            $table->string('mobile_payment_id', 20);
            $table->string('mobile_payment_phone', 20);
            $table->boolean('open')->default(false);
            $table->json('schedule')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commerces');
    }
};

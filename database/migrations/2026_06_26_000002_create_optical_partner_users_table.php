<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optical_partner_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('optical_partner_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'optical_partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('optical_partner_users');
    }
};

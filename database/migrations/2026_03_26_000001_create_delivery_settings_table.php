<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('base_cost', 8, 2)->default(1.50);
            $table->decimal('cost_per_km', 8, 2)->default(0.50);
            $table->decimal('free_km', 8, 2)->default(0.00);
            $table->decimal('fee_min', 8, 2)->default(2.00);
            $table->decimal('fee_max', 8, 2)->default(15.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_settings');
    }
};

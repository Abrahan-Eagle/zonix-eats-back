<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_observability_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedInteger('window_hours')->default(24);
            $table->unsignedInteger('orders_total')->default(0);
            $table->decimal('avg_assignment_minutes', 10, 2)->default(0);
            $table->decimal('avg_delivery_minutes', 10, 2)->default(0);
            $table->decimal('assignment_p50_minutes', 10, 2)->default(0);
            $table->decimal('assignment_p95_minutes', 10, 2)->default(0);
            $table->decimal('delivery_p50_minutes', 10, 2)->default(0);
            $table->decimal('delivery_p95_minutes', 10, 2)->default(0);
            $table->unsignedInteger('timeout_count')->default(0);
            $table->decimal('timeout_ratio_percent', 8, 2)->default(0);
            $table->decimal('agent_no_response_ratio_percent', 8, 2)->default(0);
            $table->decimal('success_ratio_percent', 8, 2)->default(0);
            $table->decimal('cancelled_ratio_percent', 8, 2)->default(0);
            $table->unsignedInteger('unassigned_over_threshold')->default(0);
            $table->unsignedInteger('frozen_tracking_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_observability_snapshots');
    }
};

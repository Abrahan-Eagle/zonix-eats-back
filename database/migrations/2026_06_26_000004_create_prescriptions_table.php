<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('optical_partner_id')->constrained()->cascadeOnDelete();
            $table->decimal('od_sphere', 5, 2)->nullable();
            $table->decimal('od_cylinder', 5, 2)->nullable();
            $table->integer('od_axis')->nullable();
            $table->decimal('oi_sphere', 5, 2)->nullable();
            $table->decimal('oi_cylinder', 5, 2)->nullable();
            $table->integer('oi_axis')->nullable();
            $table->decimal('addition', 4, 2)->nullable();
            $table->decimal('pd', 4, 1)->nullable();
            $table->decimal('pd_near', 4, 1)->nullable();
            $table->string('source')->default('manual');
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('document_id')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('patient_confirmed_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['optical_partner_id', 'status']);
            $table->index(['patient_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};

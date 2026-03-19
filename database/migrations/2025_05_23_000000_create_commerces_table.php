<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla commerces con todos los campos consolidados de migraciones "add".
     * Agregado: tax_id (required según modelo de negocio).
     */
    public function up(): void
    {
        Schema::create('commerces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(true); // Multi-restaurante: principal por perfil
            $table->string('business_name')->nullable();
            $table->string('business_type')->nullable();
            $table->string('tax_id')->nullable()->comment('Número de identificación tributaria (RUC, NIT, etc.) - Required según modelo');
            $table->text('image')->nullable();
            $table->text('address')->nullable();
            
            $table->boolean('open')->default(false);
            $table->json('schedule')->nullable();
            // Campos de membresía y comisión
            $table->enum('membership_type', ['basic', 'premium', 'enterprise'])->nullable();
            $table->decimal('membership_monthly_fee', 10, 2)->default(0);
            $table->timestamp('membership_expires_at')->nullable();
            $table->decimal('commission_percentage', 5, 2)->default(0); // Porcentaje (ej: 10.00 = 10%)
            $table->integer('cancellation_count')->default(0);
            $table->timestamp('last_cancellation_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Quitar FK desde phones.commerce_id antes de dropear commerces
        if (Schema::hasTable('phones')) {
             Schema::table('phones', function (Blueprint $table) {
                 if (DB::getDriverName() !== 'sqlite') {
                     try {
                         $table->dropForeign(['commerce_id']);
                     } catch (\Throwable $e) {
                         // Si la FK ya no existe, continuar sin fallar el rollback
                     }
                 }
             });
        }

        Schema::dropIfExists('commerces');
    }
};

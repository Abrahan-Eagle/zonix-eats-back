<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea tabla delivery_companies con nombres en inglés desde el inicio.
     * Consolidado: rename_spanish_fields_to_english (campos ya en inglés).
     */
    public function up(): void
    {
        Schema::create('delivery_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->string('name'); // En inglés desde el inicio
            $table->string('tax_id')->unique();
            $table->text('address');
            $table->boolean('active')->default(true);
            $table->enum('status', ['pending_review', 'approved', 'rejected', 'suspended'])->default('pending_review');
            $table->text('rejection_reason')->nullable();
            // Campos agregados después
            $table->text('image')->nullable()->comment('Logo de la empresa de delivery');
            $table->boolean('open')->default(false)->comment('Si la empresa está abierta/disponible');
            $table->json('schedule')->nullable()->comment('Horario de atención de la empresa');
            $table->decimal('default_payout_percentage', 5, 2)->default(70.00)->comment('Porcentaje por defecto al crear nuevos agentes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Quitar FK desde phones.delivery_company_id antes de dropear delivery_companies
        if (Schema::hasTable('phones')) {
            Schema::table('phones', function (Blueprint $table) {
                if (DB::getDriverName() !== 'sqlite') {
                    try {
                        $table->dropForeign(['delivery_company_id']);
                    } catch (\Throwable $e) {
                        // Si la FK ya no existe, continuar sin fallar el rollback
                    }
                }
            });
        }

        Schema::dropIfExists('delivery_companies');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Legacy installs: old up() added FK from commerces → business_types; drop it before dropping the table.
        if (Schema::hasTable('commerces') && Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('commerces', function (Blueprint $table) {
                try {
                    $table->dropForeign(['business_type_id']);
                } catch (\Throwable $e) {
                    // No FK (consolidated schema uses unsignedBigInteger only)
                }
            });
        }

        Schema::dropIfExists('business_types');
    }
};

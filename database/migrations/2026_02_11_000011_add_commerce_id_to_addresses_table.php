<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Vincula la dirección al comercio cuando role = 'commerce'.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('commerce_id')
                ->nullable()
                ->after('role');
            $table->foreign('commerce_id')
                ->references('id')
                ->on('commerces')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['commerce_id']);
            $table->dropColumn('commerce_id');
        });
    }
};

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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ci', 'rif'])->nullable();
            $table->integer('number_ci')->nullable();
            $table->string('taxDomicile')->nullable();
            $table->string('rif_number', 20)->nullable(); // RIF Venezuela: X-NNNNNNNN-N
            $table->string('front_image')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('approved')->default(false);
            $table->boolean('status')->default(false);
            $table->timestamps();

            $table->unsignedBigInteger('profile_id');
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

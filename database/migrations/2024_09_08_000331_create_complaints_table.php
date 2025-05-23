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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ci'); // ID (e.g., CI number)
            $table->string('email'); // Email address
            $table->text('description'); // Description of the complaint
            $table->enum('status', ['true', 'false' ])->default('false'); // Status             $table->string('first_name'); // First name
            $table->string('last_name'); // Last name
            $table->timestamps();

            // You can also add indexes if needed
            $table->index('ci');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};

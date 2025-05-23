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
        Schema::create('profiles', function (Blueprint $table) {
           $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id'); // Clave foránea

            $table->string('firstName');
            $table->string('middleName');
            $table->string('lastName');
            $table->string('secondLastName');
            $table->string('photo_users')->nullable();
            $table->date('date_of_birth');
            $table->enum('maritalStatus', ['married', 'divorced', 'single'])->default('single');
            $table->enum('sex', ['F', 'M'])->default('M');
            $table->enum('status', ['completeData', 'incompleteData', 'notverified'])->default('notverified');
            $table->unsignedBigInteger('station_id')->nullable(); // Relación opcional con stations
            $table->timestamps();

            // Relaciones
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};

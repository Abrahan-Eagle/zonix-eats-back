<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Crea tabla profiles con todos los campos consolidados de migraciones "add".
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('firstName');
            $table->string('middleName')->nullable();
            $table->string('lastName');
            $table->string('secondLastName')->nullable();
            $table->string('photo_users')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('maritalStatus', ['married', 'divorced', 'single', 'widowed'])->default('single');
            $table->enum('sex', ['F', 'M', 'O'])->default('M');
            $table->enum('status', ['completeData', 'incompleteData', 'notverified'])->default('notverified');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            // Campos de notificaciones
            $table->text('fcm_device_token')->nullable();
            $table->json('notification_preferences')->nullable();
            $table->timestamps();
            
            // Índices de performance (consolidados desde add_performance_indexes)
            $table->index('status', 'profiles_status_index');
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

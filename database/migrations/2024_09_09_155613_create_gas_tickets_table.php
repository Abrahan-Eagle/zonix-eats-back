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
        Schema::create('gas_tickets', function (Blueprint $table) {
            $table->id();
            $table->integer('queue_position')->nullable(); // Posición en la cola (1 al 200)
            $table->time('time_position');
            $table->string('qr_code')->nullable(); // QR generado
            $table->date('reserved_date'); // Fecha en que se reservó el día
            $table->date('appointment_date'); // Fecha del día que debe asistir a la cita de venta de bombona
            $table->date('expiry_date'); // Fecha de vencimiento del ticket (debe durar solo 1 día)
            $table->enum('status', ['pending', 'verifying', 'waiting', 'dispatched', 'canceled', 'expired'])->nullable();  // Estado del ticket (pendiente, verificando, espera, despachado o comprado, cancelado, expirado )  $table->boolean('asistio')->default(false);

            // Relaciones de claves foráneas
            $table->unsignedBigInteger('profile_id'); // Relación con la tabla profiles
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->unsignedBigInteger('gas_cylinders_id')->nullable(); // ID de la bombonas de gas
            $table->foreign('gas_cylinders_id')->references('id')->on('gas_cylinders')->onDelete('cascade'); // Relación con la tabla gas_cylinders
            $table->unsignedBigInteger('station_id'); // Relación obligatoria con stations
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gas_tickets');
    }
};

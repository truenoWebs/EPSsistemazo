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
        Schema::create('citas', function (Blueprint $table) {
            $table->id(); // Campo: id (Primaria, Autoincremental)
            $table->unsignedBigInteger('id_paciente'); // Campo: id_paciente (Llave foránea)
            $table->unsignedBigInteger('id_medico'); // Campo: id_medico (Llave foránea)
            $table->unsignedBigInteger('id_especialidad_cita'); // Campo: id_especialidad_cita (Llave foránea para la especialidad de la cita)
            $table->dateTime('fecha_hora_cita'); // Campo: fecha_hora_cita
            $table->string('estado_cita', 50)->default('Programada'); // Campo: estado_cita (Ej: Programada, Cancelada, Realizada, No Asistió)
            $table->text('motivo_cita')->nullable(); // Campo: motivo_cita
            $table->text('observaciones_cita')->nullable(); // Campo: observaciones_cita (para el médico)
            $table->timestamps(); // Campos: created_at, updated_at

            // Definición de las llaves foráneas
            $table->foreign('id_paciente')->references('id')->on('pacientes')->onDelete('cascade');
            $table->foreign('id_medico')->references('id')->on('medicos')->onDelete('cascade');
            $table->foreign('id_especialidad_cita')->references('id')->on('especialidades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};

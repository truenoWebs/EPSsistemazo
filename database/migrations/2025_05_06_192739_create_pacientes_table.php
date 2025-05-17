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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id(); // Campo: id (Primaria, Autoincremental)
            $table->string('numero_documento_paciente', 20)->unique(); // Campo: numero_documento_paciente
            $table->string('tipo_documento_paciente', 50); // Campo: tipo_documento_paciente
            $table->string('nombres_paciente', 100); // Campo: nombres_paciente
            $table->string('apellidos_paciente', 100); // Campo: apellidos_paciente
            $table->date('fecha_nacimiento_paciente'); // Campo: fecha_nacimiento_paciente
            $table->string('genero_paciente', 20)->nullable(); // Campo: genero_paciente (Ej: Masculino, Femenino, Otro)
            $table->string('direccion_paciente', 255)->nullable(); // Campo: direccion_paciente
            $table->string('telefono_paciente', 20)->nullable(); // Campo: telefono_paciente
            $table->string('email_paciente', 100)->unique()->nullable(); // Campo: email_paciente
            $table->string('eps_paciente', 100)->nullable(); // Campo: eps_paciente
            $table->timestamps(); // Campos: created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};

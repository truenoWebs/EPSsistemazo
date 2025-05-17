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
        Schema::create('medicos', function (Blueprint $table) {
            $table->id(); // Campo: id (Primaria, Autoincremental)
            $table->string('numero_documento_medico', 20)->unique(); // Campo: numero_documento_medico
            $table->string('tipo_documento_medico', 50); // Campo: tipo_documento_medico (Ej: Cédula de Ciudadanía, Cédula de Extranjería)
            $table->string('nombres_medico', 100); // Campo: nombres_medico
            $table->string('apellidos_medico', 100); // Campo: apellidos_medico
            $table->string('tarjeta_profesional', 50)->unique(); // Campo: tarjeta_profesional
            $table->string('telefono_medico', 20)->nullable(); // Campo: telefono_medico
            $table->string('email_medico', 100)->unique()->nullable(); // Campo: email_medico
            $table->unsignedBigInteger('id_especialidad'); // Campo: id_especialidad (Llave foránea)
            $table->timestamps(); // Campos: created_at, updated_at

            // Definición de la llave foránea
            $table->foreign('id_especialidad')->references('id')->on('especialidades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};

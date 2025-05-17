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
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id(); // Campo: id (Primaria, Autoincremental)
            $table->string('nombre_especialidad', 100)->unique(); // Campo: nombre_especialidad
            $table->text('descripcion_especialidad')->nullable(); // Campo: descripcion_especialidad
            $table->timestamps(); // Campos: created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidades');
    }
};

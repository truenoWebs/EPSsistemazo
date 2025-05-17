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
        Schema::table('usuarios', function (Blueprint $table) {
            // Llave foránea para paciente_id
            $table->foreign('paciente_id')
                ->references('id') // Asumiendo que el id en 'pacientes' es 'id'
                ->on('pacientes')
                ->onDelete('set null'); // O 'cascade' o 'restrict' según tu lógica de negocio.

            // Llave foránea para medico_id
            $table->foreign('medico_id')
                ->references('id') // Asumiendo que el id en 'medicos' es 'id'
                ->on('medicos')
                ->onDelete('set null'); // O 'cascade' o 'restrict'.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Es buena práctica nombrar las restricciones para poder eliminarlas específicamente.
            // Si Laravel las nombró automáticamente, esto podría funcionar:
            $table->dropForeign(['paciente_id']);
            $table->dropForeign(['medico_id']);
            // Si no, necesitarías $table->dropForeign('nombre_de_la_constraint');
        });
    }
};


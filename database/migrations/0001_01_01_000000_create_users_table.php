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
        // 1. Tabla de Usuarios (Modificada)
        // Renombramos la tabla a 'usuarios' y traducimos solo los campos seguros.
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); // Campo 'id' (PK, AI) - Se mantiene en inglés por convención y compatibilidad.
            $table->string('nombre_usuario'); // Antes 'name', traducido a español.
            $table->string('email')->unique(); // Campo 'email' - Se mantiene en inglés, es un identificador estándar.
            $table->timestamp('email_verificado_en')->nullable(); // Antes 'email_verified_at', traducido.
            $table->string('contrasena'); // Antes 'password', traducido.

            // Nuevo campo para el rol del usuario
            $table->string('rol_usuario', 50)->default('paciente'); // Ej: paciente, medico, administrador

            // Nuevas columnas para futuras llaves foráneas (sin la restricción de FK aún)
            // Estos nombres pueden estar en español ya que son personalizados para nuestra lógica.
            $table->unsignedBigInteger('paciente_id')->nullable();
            $table->unsignedBigInteger('medico_id')->nullable();

            $table->rememberToken(); // Campo 'remember_token' - Se mantiene en inglés, esencial para la función "Recordarme".
            $table->timestamps(); // Campos 'created_at', 'updated_at' - Se mantienen en inglés para compatibilidad con Eloquent.
            // Si necesitas que estos también estén en español, se requiere configuración adicional en el modelo.
        });

        // 2. Tabla de Tokens de Restablecimiento de Contraseña (Sin cambios en nombres de tabla/columnas)
        // Es MUY RECOMENDABLE mantener esta tabla y sus columnas con los nombres por defecto de Laravel.
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. Tabla de Sesiones (Sin cambios en nombres de tabla/columnas)
        // Es MUY RECOMENDABLE mantener esta tabla y sus columnas con los nombres por defecto de Laravel.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index(); // 'user_id' es esperado por Laravel.
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios'); // Revertir la tabla 'usuarios'
        Schema::dropIfExists('password_reset_tokens'); // Revertir con el nombre original
        Schema::dropIfExists('sessions'); // Revertir con el nombre original
    }
};

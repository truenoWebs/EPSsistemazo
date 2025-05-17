<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory;

    protected $table = 'medicos'; // Especifica el nombre de la tabla

    protected $fillable = [
        'numero_documento_medico',
        'tipo_documento_medico',
        'nombres_medico',
        'apellidos_medico',
        'tarjeta_profesional',
        'telefono_medico',
        'email_medico',
        'id_especialidad',
    ];

    // Relación: Un médico pertenece a una especialidad
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'id_especialidad');
    }

    // Relación: Un médico puede tener muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_medico');
    }

    // Relación: Un médico puede estar asociado a una cuenta de usuario
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'medico_id');
    }
}

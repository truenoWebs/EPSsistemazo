<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes'; // Especifica el nombre de la tabla

    protected $fillable = [
        'numero_documento_paciente',
        'tipo_documento_paciente',
        'nombres_paciente',
        'apellidos_paciente',
        'fecha_nacimiento_paciente',
        'genero_paciente',
        'direccion_paciente',
        'telefono_paciente',
        'email_paciente',
        'eps_paciente',
    ];

    // Relación: Un paciente puede tener muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_paciente');
    }

    // Relación: Un paciente puede estar asociado a una cuenta de usuario
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'paciente_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas'; // Especifica el nombre de la tabla

    protected $fillable = [
        'id_paciente',
        'id_medico',
        'id_especialidad_cita',
        'fecha_hora_cita',
        'estado_cita',
        'motivo_cita',
        'observaciones_cita',
    ];

    protected $casts = [
        'fecha_hora_cita' => 'datetime',
    ];

    // Relación: Una cita pertenece a un paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'id_paciente');
    }

    // Relación: Una cita pertenece a un médico
    public function medico()
    {
        return $this->belongsTo(Medico::class, 'id_medico');
    }

    // Relación: Una cita pertenece a una especialidad
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'id_especialidad_cita');
    }
}

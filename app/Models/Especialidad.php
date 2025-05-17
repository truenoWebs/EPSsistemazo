<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades'; // Especifica el nombre de la tabla

    protected $fillable = [
        'nombre_especialidad',
        'descripcion_especialidad',
    ];

    // Relación: Una especialidad puede tener muchos médicos
    public function medicos()
    {
        return $this->hasMany(Medico::class, 'id_especialidad');
    }

    // Relación: Una especialidad puede estar en muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_especialidad_cita');
    }
}

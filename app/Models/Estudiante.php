<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiante';

    protected $primaryKey = 'id_usuario';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'fecha_nacimiento',
        'cedula',
        'deuda'
    ];

    protected $casts = [
        'deuda' => 'boolean',
        'fecha_nacimiento' => 'date'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_estudiante');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'id_estudiante');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_estudiante');
    }

    public function listaEspera()
    {
        return $this->hasMany(Lista_espera::class, 'id_estudiante');
    }
}
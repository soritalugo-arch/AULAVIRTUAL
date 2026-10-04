<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $table = 'curso';

    protected $primaryKey = 'id_curso';

    protected $fillable = [
        'nombre',
        'limite_estudiantes',
    ];

    protected $casts = [
        'limite_estudiantes' => 'integer'
    ];

    public function carreras()
    {
        return $this->belongsToMany(Carrera::class, 'curso_carrera', 'curso_id', 'carrera_id');
    }

    public function cuatrimestres()
    {
        return $this->belongsToMany(Cuatrimestre::class, 'curso_cuatrimestre', 'curso_id', 'cuatrimestre_id');
    }

    public function profesores()
    {
        return $this->belongsToMany(Profesor::class, 'curso_profesor', 'curso_id', 'profesor_id');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_curso');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'id_curso');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'id_curso');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_curso');
    }

    public function listaEspera()
    {
        return $this->hasMany(Lista_espera::class, 'id_curso');
    }
}

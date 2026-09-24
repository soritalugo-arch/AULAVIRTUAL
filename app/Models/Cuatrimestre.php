<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuatrimestre extends Model
{
    use HasFactory;

    protected $table = 'cuatrimestre';

    protected $primaryKey = 'id_cuatrimestre';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_cuatrimestre', 'cuatrimestre_id', 'curso_id');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'id_cuatrimestre');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_cuatrimestre');
    }
}

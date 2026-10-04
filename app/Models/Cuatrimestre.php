<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuatrimestre extends Model
{
    use HasFactory;

    protected $table = 'cuatrimestre';

    protected $primaryKey = 'id_cuatrimestre';

    /** Matrícula abierta: los estudiantes pueden inscribirse o retirarse. */
    public const ESTADO_MATRICULA = 'matriculacion';

    /** Clases en marcha: se califica y se registra asistencia. */
    public const ESTADO_EN_CURSO = 'en_curso';

    /** Período finalizado: histórico, solo lectura. */
    public const ESTADO_CERRADO = 'cerrado';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'estado'
    ];

    /** Los tres estados posibles de un cuatrimestre. */
    public static function estados(): array
    {
        return [
            self::ESTADO_MATRICULA,
            self::ESTADO_EN_CURSO,
            self::ESTADO_CERRADO,
        ];
    }

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

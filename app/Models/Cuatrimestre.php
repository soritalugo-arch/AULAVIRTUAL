<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuatrimestre extends Model
{
    use HasFactory;

    protected $table = 'cuatrimestre';

    protected $primaryKey = 'id_cuatrimestre';

    /** Período nuevo, todavía sin matrícula: se está armando la oferta. */
    public const ESTADO_PRE_MATRICULA = 'pre_matricula';

    /** Matrícula abierta: los estudiantes pueden inscribirse o retirarse. */
    public const ESTADO_MATRICULA = 'matriculacion';

    /** Clases en marcha: se califica y se registra asistencia. */
    public const ESTADO_EN_CURSO = 'en_curso';

    /** Período terminado: histórico, solo lectura. */
    public const ESTADO_FINALIZADO = 'finalizado';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'estado'
    ];

    /** Los cuatro momentos de un período, en el orden en que se recorren. */
    public static function estados(): array
    {
        return [
            self::ESTADO_PRE_MATRICULA,
            self::ESTADO_MATRICULA,
            self::ESTADO_EN_CURSO,
            self::ESTADO_FINALIZADO,
        ];
    }

    /**
     * Los dos estados en los que el período es el que está pasando.
     *
     * Mientras el período está en pre-matrícula o en matrícula todavía se puede
     * cambiar de momento; en cursado también. Solo cuando pasa a finalizado se
     * convierte en pasado.
     */
    public static function estadosPresentes(): array
    {
        return [
            self::ESTADO_MATRICULA,
            self::ESTADO_EN_CURSO,
        ];
    }

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];

    /**
     * El período que está pasando, el único con el que se trabaja.
     *
     * Se decide por el estado y no por las fechas a propósito: el instituto abre
     * y cierra la matrícula cuando quiere, así que un período que todavía no
     * empezó puede estar en matrícula, y en la pausa entre dos períodos no hay
     * ningún rango de fechas que contenga el día de hoy. Preguntar por el estado
     * evita que en esa pausa el sistema se quede sin período y rompa la
     * matrícula, las notas y la asistencia.
     *
     * El desempate es el más reciente de los que están presentes: si por un
     * error de captura quedaran dos, manda el último abierto.
     */
    public static function cuatrimestrePresente(): ?self
    {
        return static::query()
            ->whereIn('estado', self::estadosPresentes())
            ->orderByDesc('fecha_inicio')
            ->first();
    }

    /** ¿Este es el período que está pasando? */
    public function esPresente(): bool
    {
        return in_array($this->estado, self::estadosPresentes(), true);
    }

    /** ¿Este período ya terminó y quedó como histórico? */
    public function esFinalizado(): bool
    {
        return $this->estado === self::ESTADO_FINALIZADO;
    }

    /** ¿Este período todavía no se puede trabajar: está armándose o matriculando? */
    public function estaAbierto(): bool
    {
        return $this->esPresente();
    }

    /** Períodos presentes: los que no son históricos. */
    public function scopePresentes(Builder $query): Builder
    {
        return $query->whereIn('estado', self::estadosPresentes());
    }

    /** Períodos ya terminados, del más reciente al más antiguo. */
    public function scopeFinalizados(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_FINALIZADO);
    }

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

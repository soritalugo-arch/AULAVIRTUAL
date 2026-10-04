<?php

namespace App\Models;

use App\Services\ParcialService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    use HasFactory;

    protected $table = 'calificacion';

    protected $primaryKey = 'id_calificacion';

    protected $fillable = [
        'id_estudiante',
        'id_curso',
        'id_cuatrimestre',
        'nota',
        'parcial1',
        'parcial2',
        'parcial3',
        'parcial4',
        'promedio',
        'tiene_parciales',
        'observaciones'
    ];

    protected $casts = [
        'nota' => 'integer',
        'parcial1' => 'float',
        'parcial2' => 'float',
        'parcial3' => 'float',
        'parcial4' => 'float',
        'promedio' => 'float',
        'tiene_parciales' => 'boolean'
    ];

    /**
     * Las cuatro parciales de la fila, en orden.
     *
     * @return array<int, float|null>
     */
    public function parciales(): array
    {
        return [
            $this->parcial1,
            $this->parcial2,
            $this->parcial3,
            $this->parcial4,
        ];
    }

    /**
     * Acumulado sobre 100: la suma de las cuatro parciales.
     *
     * Las filas anteriores a las parciales no lo tienen (no existe de donde
     * sumarlo): devuelven null y las vistas muestran un guion.
     */
    public function acumulado(): ?float
    {
        return ParcialService::acumulado($this->parciales());
    }

    /**
     * La nota que manda: el promedio de las parciales si las hay, y la nota
     * final antigua si la fila todavia no tiene parciales.
     *
     * Toda la institución lee el mismo numero por este metodo: el veredicto del
     * profesor, el historial del estudiante y el certificado.
     */
    public function notaEfectiva(): ?float
    {
        if ($this->tiene_parciales && $this->promedio !== null) {
            return (float) $this->promedio;
        }

        return $this->nota === null ? null : (float) $this->nota;
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function cuatrimestre()
    {
        return $this->belongsTo(Cuatrimestre::class, 'id_cuatrimestre');
    }
}

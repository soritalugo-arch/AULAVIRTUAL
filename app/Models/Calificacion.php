<?php

namespace App\Models;

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
        'observaciones'
    ];

    protected $casts = [
        'nota' => 'integer'
    ];

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

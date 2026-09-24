<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencia';

    protected $primaryKey = 'id_asistencia';

    protected $fillable = [
        'id_estudiante',
        'id_curso',
        'id_cuatrimestre',
        'fecha',
        'presente'
    ];

    protected $casts = [
        'fecha' => 'date',
        'presente' => 'boolean'
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

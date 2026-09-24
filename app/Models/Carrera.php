<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    use HasFactory;

    protected $table = 'carrera';

    protected $primaryKey = 'id_carrera';

    protected $fillable = [
        'nombre',
        'duracion'
    ];

    protected $casts = [
        'duracion' => 'integer'
    ];

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_carrera', 'carrera_id', 'curso_id');
    }
}

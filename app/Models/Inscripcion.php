<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscripcion';

    protected $primaryKey = 'id_inscripcion';

    protected $fillable = [
        'id_estudiante',
        'id_curso',
        'fecha_inscripcion'
    ];
}

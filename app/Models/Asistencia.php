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
}

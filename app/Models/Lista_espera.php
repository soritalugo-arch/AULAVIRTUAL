<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lista_espera extends Model
{
    use HasFactory;

    protected $table = 'lista_espera';

    protected $primaryKey = 'idlista_espera';

    protected $fillable = [
        'id_curso',
        'id_estudiante'
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }
}

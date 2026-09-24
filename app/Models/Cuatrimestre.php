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
}

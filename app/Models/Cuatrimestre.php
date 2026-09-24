<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuatrimestre extends Model
{
    use HasFactory;

    protected $table = 'cuatrimestre';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiante';

    protected $primaryKey = 'id_usuario';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'fecha_nacimiento',
        'cedula',
        'deuda'
    ];

    protected $casts = [
        'deuda' => 'boolean',
        'fecha_nacimiento' => 'date'
    ];
}
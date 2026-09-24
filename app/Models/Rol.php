<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';

    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre'
    ];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'rol_usuario', 'rol_id', 'usuario_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombres',
        'apellidos',
        'telefono',
        'email',
        'password'
    ];

    protected $casts = [
        'password' => 'hashed'
    ];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'usuario_id', 'rol_id');
    }

    public function profesor()
    {
        return $this->hasOne(Profesor::class, 'id_usuario');
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'id_usuario');
    }
}

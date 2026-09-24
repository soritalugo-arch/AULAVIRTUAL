<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory;
    use Notifiable;
     
    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';
    protected $hidden = ['password'];
    protected $fillable = [
        'nombres',
        'apellidos',
        'telefono',
        'email',
        'password'
    ];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'usuario_id', 'rol_id');
    }
}

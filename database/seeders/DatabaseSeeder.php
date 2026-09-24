<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRol = Rol::create(['nombre' => 'admin']);
        $profesorRol = Rol::create(['nombre' => 'profesor']);
        $estudianteRol = Rol::create(['nombre' => 'estudiante']);

        $admin = Usuario::factory()->create([
            'nombres' => 'Rectora',
            'apellidos' => 'Aula',
            'email' => 'rectora@aula.edu',
        ]);
        $admin->roles()->attach($adminRol->id_rol);

        $profesor = Usuario::factory()->create([
            'nombres' => 'Profesor',
            'apellidos' => 'Demo',
            'email' => 'profesor@aula.edu',
        ]);
        $profesor->roles()->attach($profesorRol->id_rol);
        Profesor::create(['id_usuario' => $profesor->id_usuario]);

        $estudiante = Usuario::factory()->create([
            'nombres' => 'Estudiante',
            'apellidos' => 'Demo',
            'email' => 'estudiante@aula.edu',
        ]);
        $estudiante->roles()->attach($estudianteRol->id_rol);
        Estudiante::create([
            'id_usuario' => $estudiante->id_usuario,
            'fecha_nacimiento' => '2000-01-15',
            'cedula' => '12345678',
            'deuda' => false,
        ]);
    }
}
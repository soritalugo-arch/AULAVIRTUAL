<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuarioSeeder extends Seeder
{
    private const ESTUDIANTES_FIJOS = [
        DatabaseSeeder::EMAIL_ESTUDIANTE => ['Alejandro Rafael', 'González'],
        DatabaseSeeder::EMAIL_DEUDA => ['Gabriela Josefina', 'Castillo'],
        DatabaseSeeder::EMAIL_EGRESADA => ['Valentina Alejandra', 'Rojas'],
        DatabaseSeeder::EMAIL_CONFLICTO => ['Luis Enrique', 'Marcano'],
        DatabaseSeeder::EMAIL_INASISTENTE => ['Carlos Alberto', 'Fuentes'],
        DatabaseSeeder::EMAIL_ALERTA => ['Andreína del Valle', 'Quintero'],
        DatabaseSeeder::EMAIL_REPITIENTE => ['Diego Alejandro', 'Tovar'],
        DatabaseSeeder::EMAIL_PUNTUAL => ['Sofía Carolina', 'Méndez'],
    ];

    public function run(): void
    {
        $admin = Usuario::factory()->create([
            'nombres' => 'María Teresa',
            'apellidos' => 'Rodríguez',
            'email' => DatabaseSeeder::EMAIL_ADMIN,
            'password' => DatabaseSeeder::CONTRASENA_DE_EMAIL[DatabaseSeeder::EMAIL_ADMIN],
        ]);
        $this->adjuntarRol($admin->id_usuario, 'admin');

        $profesorDemo = Usuario::factory()->create([
            'nombres' => 'José Gregorio',
            'apellidos' => 'Salazar',
            'email' => DatabaseSeeder::EMAIL_PROFESOR,
            'password' => DatabaseSeeder::CONTRASENA_DE_EMAIL[DatabaseSeeder::EMAIL_PROFESOR],
        ]);
        $profesores = Usuario::factory()->count(24)->create()->pluck('id_usuario')->push($profesorDemo->id_usuario);

        $rolProfesor = Rol::where('nombre', 'profesor')->value('id_rol');
        DB::table('rol_usuario')->insert(
            $profesores->map(fn ($id) => [
                'rol_id' => $rolProfesor,
                'usuario_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all()
        );

        foreach (self::ESTUDIANTES_FIJOS as $email => $nombres) {
            $usuario = Usuario::factory()->create([
                'nombres' => $nombres[0],
                'apellidos' => $nombres[1],
                'email' => $email,
                'password' => DatabaseSeeder::CONTRASENA_DE_EMAIL[$email],
            ]);
            $this->adjuntarRol($usuario->id_usuario, 'estudiante');
        }

        $estudiantes = Usuario::factory()->count(592)->create()->pluck('id_usuario');
        $rolEstudiante = Rol::where('nombre', 'estudiante')->value('id_rol');
        DB::table('rol_usuario')->insert(
            $estudiantes->map(fn ($id) => [
                'rol_id' => $rolEstudiante,
                'usuario_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all()
        );
    }

    private function adjuntarRol(int $usuarioId, string $nombre): void
    {
        $rolId = Rol::where('nombre', $nombre)->value('id_rol');
        DB::table('rol_usuario')->insertOrIgnore([
            'rol_id' => $rolId,
            'usuario_id' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstudianteSeeder extends Seeder
{
    private const PERFILES_FIJOS = [
        DatabaseSeeder::EMAIL_ESTUDIANTE => ['18765432', '2001-05-10', false],
        DatabaseSeeder::EMAIL_DEUDA => ['10111213', '1999-02-14', true],
        DatabaseSeeder::EMAIL_EGRESADA => ['13141516', '1998-08-20', false],
        DatabaseSeeder::EMAIL_CONFLICTO => ['15161718', '2000-11-30', false],
        DatabaseSeeder::EMAIL_INASISTENTE => ['16171819', '2002-03-15', false],
        DatabaseSeeder::EMAIL_ALERTA => ['17181920', '2001-09-05', false],
        DatabaseSeeder::EMAIL_REPITIENTE => ['18192021', '2000-01-25', false],
        DatabaseSeeder::EMAIL_PUNTUAL => ['19202122', '2002-12-01', false],
    ];

    public function run(): void
    {
        foreach (self::PERFILES_FIJOS as $email => $datos) {
            $usuario = Usuario::where('email', $email)->first();
            DB::table('estudiante')->insertOrIgnore([
                'id_usuario' => $usuario->id_usuario,
                'fecha_nacimiento' => $datos[1],
                'cedula' => $datos[0],
                'deuda' => $datos[2],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $ids = Usuario::whereHas('roles', fn ($q) => $q->where('nombre', 'estudiante'))
            ->whereNotIn('email', array_keys(self::PERFILES_FIJOS))
            ->orderBy('id_usuario')
            ->pluck('id_usuario');

        $rows = [];
        foreach ($ids as $i => $id) {
            $rows[] = [
                'id_usuario' => $id,
                'fecha_nacimiento' => fake()->dateTimeBetween('-26 years', '-17 years')->format('Y-m-d'),
                'cedula' => fake()->unique()->numerify('2#######'),
                'deuda' => $i % 10 === 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 500) as $lote) {
            DB::table('estudiante')->insert($lote);
        }
    }
}
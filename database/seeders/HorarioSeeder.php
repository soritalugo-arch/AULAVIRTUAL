<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Horario;
use Illuminate\Database\Seeder;

class HorarioSeeder extends Seeder
{
    public const SLOTS = [
        ['dia' => 'Lunes', 'hora_inicio' => '18:00:00', 'hora_fin' => '20:00:00'],
        ['dia' => 'Martes', 'hora_inicio' => '18:00:00', 'hora_fin' => '20:00:00'],
        ['dia' => 'Miércoles', 'hora_inicio' => '18:00:00', 'hora_fin' => '20:00:00'],
        ['dia' => 'Jueves', 'hora_inicio' => '18:00:00', 'hora_fin' => '20:00:00'],
        ['dia' => 'Viernes', 'hora_inicio' => '18:00:00', 'hora_fin' => '20:00:00'],
        ['dia' => 'Sábado', 'hora_inicio' => '08:00:00', 'hora_fin' => '10:00:00'],
        ['dia' => 'Sábado', 'hora_inicio' => '10:00:00', 'hora_fin' => '12:00:00'],
        ['dia' => 'Lunes', 'hora_inicio' => '16:00:00', 'hora_fin' => '18:00:00'],
    ];

    public function run(): void
    {
        foreach (Curso::orderBy('id_curso')->get() as $curso) {
            $slot = self::SLOTS[self::slotDe($curso)];

            Horario::create([
                'id_curso' => $curso->id_curso,
                'dia_semana' => $slot['dia'],
                'hora_inicio' => $slot['hora_inicio'],
                'hora_fin' => $slot['hora_fin'],
            ]);
        }
    }

    public static function slotDe(Curso $curso): int
    {
        if (in_array($curso->nombre, DatabaseSeeder::CURSOS_CONFLICTO, true)) {
            return 0;
        }

        return ($curso->id_curso - 1) % 8;
    }
}
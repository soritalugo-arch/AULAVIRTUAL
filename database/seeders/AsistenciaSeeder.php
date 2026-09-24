<?php

namespace Database\Seeders;

use App\Models\Asistencia;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Horario;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AsistenciaSeeder extends Seeder
{
    private const MAPA_DIA = [
        'Domingo' => 0,
        'Lunes' => 1,
        'Martes' => 2,
        'Miércoles' => 3,
        'Jueves' => 4,
        'Viernes' => 5,
        'Sábado' => 6,
    ];

    private const TOTAL_CLASES = 12;

    public function run(): void
    {
        $pasado = Cuatrimestre::orderBy('fecha_inicio')->first()->id_cuatrimestre;
        $idPorEmail = Usuario::whereIn('email', [
            DatabaseSeeder::EMAIL_INASISTENTE,
            DatabaseSeeder::EMAIL_ALERTA,
        ])->pluck('id_usuario', 'email');

        $horarios = Horario::all()->keyBy('id_curso');
        $nombresDeCurso = Curso::all()->pluck('nombre', 'id_curso');

        $filas = [];

        foreach (CalificacionSeeder::$paresQ1 as $sid => $cursosDelEstudiante) {
            foreach ($cursosDelEstudiante as $cid => $nota) {
                $horario = $horarios[$cid];
                $dia = self::MAPA_DIA[$horario->dia_semana];
                $fechas = $this->fechasClases($dia);

                foreach ($fechas as $k => $fecha) {
                    $filas[] = [
                        'id_estudiante' => $sid,
                        'id_curso' => $cid,
                        'id_cuatrimestre' => $pasado,
                        'fecha' => $fecha,
                        'presente' => ! $this->falta($sid, $cid, $k, $idPorEmail, $nombresDeCurso),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($filas, 1000) as $lote) {
            Asistencia::insert($lote);
        }
    }

    private function fechasClases(int $dia): array
    {
        $inicio = Carbon::parse('2026-05-04');
        $fin = Carbon::parse('2026-07-31');

        $fechas = [];
        $fecha = $inicio->copy()->next($dia);

        while (count($fechas) < self::TOTAL_CLASES && $fecha->lte($fin)) {
            $fechas[] = $fecha->toDateString();
            $fecha->addWeek();
        }

        return $fechas;
    }

    private function falta(int $sid, int $cid, int $clase, $idPorEmail, $nombresDeCurso): bool
    {
        $cursoFundamentos = 'Fundamentos de Programación';
        $curso = $nombresDeCurso[$cid];

        if ($sid === $idPorEmail[DatabaseSeeder::EMAIL_INASISTENTE] && $curso === $cursoFundamentos) {
            return $clase < 5;
        }

        if ($sid === $idPorEmail[DatabaseSeeder::EMAIL_ALERTA] && $curso === $cursoFundamentos) {
            return $clase < 3;
        }

        return (($sid * 7 + $cid * 13 + $clase) % 11) === 0;
    }
}
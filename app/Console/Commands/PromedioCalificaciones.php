<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Cuatrimestre;
use App\Services\CalificacionAsistenciaService;
use Illuminate\Console\Command;

class PromedioCalificaciones extends Command
{
    // uso: php artisan notas:promedio [--curso=id] [--cuatrimestre=id]
    protected $signature = 'notas:promedio
                            {--curso= : id del curso; omitir para mostrar todos}
                            {--cuatrimestre= : id del cuatrimestre; omitir para usar el mas reciente}';
    protected $description = 'muestra el promedio de notas por curso y cuatrimestre';
    public function __construct(private CalificacionAsistenciaService $servicio)
    {
        parent::__construct();
    }
    public function handle(): int
    {
        $idCurso        = $this->option('curso')        ? (int) $this->option('curso')        : null;
        $idCuatrimestre = $this->option('cuatrimestre') ? (int) $this->option('cuatrimestre') : null;
        // resolver cuatrimestre
        if (!$idCuatrimestre) {
            $cuatrimestre = Cuatrimestre::orderByDesc('id_cuatrimestre')->first();
            if (!$cuatrimestre) {
                $this->error('no hay cuatrimestres registrados.');
                return Command::FAILURE;
            }
            $idCuatrimestre = $cuatrimestre->id_cuatrimestre;
            $this->line("cuatrimestre no indicado; usando el mas reciente: #{$idCuatrimestre}");
        } else {
            $cuatrimestre = Cuatrimestre::find($idCuatrimestre);
            if (!$cuatrimestre) {
                $this->error("cuatrimestre #{$idCuatrimestre} no existe.");
                return Command::FAILURE;
            }
        }
        // resolver cursos
        $query = Curso::query();
        if ($idCurso) $query->where('id_curso', $idCurso);
        $cursos = $query->get();
        if ($cursos->isEmpty()) {
            $this->warn('no se encontraron cursos con los filtros indicados.');
            return Command::SUCCESS;
        }
        $this->line("periodo: cuatrimestre #{$idCuatrimestre} ({$cuatrimestre->fecha_inicio} - {$cuatrimestre->fecha_fin})");
        $this->newLine();
        $filas = [];
        foreach ($cursos as $curso) {
            $promedio = $this->servicio->promedioPorCurso($curso->id_curso, $idCuatrimestre);
            $filas[]  = [$curso->id_curso, $curso->nombre, $promedio !== null ? number_format($promedio, 2) : 'sin notas'];
        }
        $this->table(['id curso', 'nombre del curso', 'promedio'], $filas);
        $this->newLine();
        $this->line('calculo completado.');
        return Command::SUCCESS;
    }
}

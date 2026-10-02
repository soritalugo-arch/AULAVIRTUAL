<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\HistorialService;
use Database\Seeders\CursoSeeder;
use Illuminate\Support\Collection;

/**
 * Plan de estudios del estudiante: el recorrido de su carrera, cuatrimestre a
 * cuatrimestre (las "etapas" del plan), con la situacion real de cada materia:
 * aprobada, en curso o pendiente.
 *
 * Igual que el historial, no recibe ningun id: la carrera sale del estudiante
 * autenticado, de modo que un alumno solo ve su plan.
 */
class PlanEstudiosController extends Controller
{
    public function __construct(private HistorialService $historial) {}

    public function index()
    {
        $estudiante = request()->user()->estudiante;
        $carrera = $estudiante?->carrera;

        $datos = $this->historial->historial($estudiante);

        $aprobadas = collect($datos['periodos'])
            ->flatMap(fn ($p) => $p['cursos']->all())
            ->filter(fn ($f) => $f['estado'] === 'Aprobado')
            ->pluck('id_curso');

        $enCurso = $datos['enCurso']->pluck('id_curso');

        $etapas = $this->etapas($carrera?->cursos()->withPivot('etapa')->get(), $aprobadas, $enCurso);

        return view('estudiante.plan', [
            'estudiante' => $estudiante,
            'carrera' => $carrera,
            'etapas' => $etapas,
            'totalMaterias' => $etapas->sum(fn ($e) => $e['materias']->count()),
            'aprobadas' => $etapas->sum(fn ($e) => $e['materias']->where('estado', 'Aprobada')->count()),
        ]);
    }

    /**
     * Materias de la carrera agrupadas de la etapa 1 hasta el ultimo
     * cuatrimestre del plan, cada una con su estado para el estudiante.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\Curso>|null  $cursos
     * @param  Collection<int, int>  $aprobadas
     * @param  Collection<int, int>  $enCurso
     */
    private function etapas($cursos, Collection $aprobadas, Collection $enCurso): Collection
    {
        $porEtapa = collect($cursos ?? [])
            ->sortBy([['pivot.etapa', 'asc'], ['nombre', 'asc']])
            ->groupBy('pivot.etapa');

        $totalEtapas = $cursos && $cursos->isNotEmpty()
            ? $porEtapa->keys()->max()
            : 0;

        $etapas = collect();

        for ($etapa = 1; $etapa <= $totalEtapas; $etapa++) {
            $materias = $porEtapa->get($etapa, collect())->map(function ($curso) use ($aprobadas, $enCurso) {
                return [
                    'nombre' => $curso->nombre,
                    'base' => in_array($curso->nombre, CursoSeeder::materiasBase(), true),
                    'estado' => $aprobadas->contains($curso->id_curso)
                        ? 'Aprobada'
                        : ($enCurso->contains($curso->id_curso) ? 'En curso' : 'Pendiente'),
                ];
            })->values();

            $etapas->push([
                'numero' => $etapa,
                'materias' => $materias,
            ]);
        }

        return $etapas;
    }
}
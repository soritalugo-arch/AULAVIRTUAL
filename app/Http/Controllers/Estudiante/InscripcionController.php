<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Lista_espera;
use App\Services\HistorialService;
use App\Services\InscripcionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InscripcionController extends Controller
{
    protected InscripcionService $inscripcionService;

    public function __construct(
        InscripcionService $inscripcionService,
        private HistorialService $historial
    ) {
        $this->inscripcionService = $inscripcionService;
    }

    /**
     * Muestra la vista de matriculación / desmatriculación con las asignaturas disponibles.
     */
    public function index()
    {
        // Obtener el estudiante autenticado con su carrera
        $estudiante = Estudiante::with('carrera')->where('id_usuario', Auth::id())->firstOrFail();
        $carrera = $estudiante->carrera;

        // Cuatrimestre vigente (período cuya fecha actual cae dentro de su rango)
        $cuatrimestreVigente = Cuatrimestre::where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->first();

        // Ventana de cuatrimestres del plan que le tocan en esta inscripción
        $ventana = $this->historial->ventanaEtapas($estudiante);

        $totalEtapas = $ventana['totalEtapas'] ?? null;
        $formato = $ventana['formato'] ?? null;

       // NUEVO: 1. Obtener los IDs de los cursos que el estudiante ya cursó y aprobó
        // Buscamos en las calificaciones del estudiante donde la nota o el promedio sea aprobatorio (>= 10)
        $cursosAprobadosIds = $estudiante->calificaciones()
            ->where(function($query) {
                // Si usa el sistema antiguo (nota directa)
                $query->where('nota', '>=', 6)
                      // O si usa el sistema nuevo (tiene parciales y promedio calculado)
                      ->orWhere('promedio', '>=', 6);
            })
            ->pluck('id_curso')
            ->toArray();
        // Oferta académica
        $cursos = $carrera && $cuatrimestreVigente && $ventana
            ? Curso::with(['horarios', 'profesores'])
                ->withCount('inscripciones')
                ->whereHas('carreras', fn ($q) => $q
                    ->whereKey($carrera->id_carrera)
                    ->whereIn('curso_carrera.etapa', $ventana['etapas']))
                ->whereHas('cuatrimestres', fn ($q) => $q->whereKey($cuatrimestreVigente->id_cuatrimestre))
                // NUEVO: 2. Excluir de la oferta los cursos que ya aprobó
                ->whereNotIn('id_curso', $cursosAprobadosIds) 
                ->get()
            : collect();

        // Cursos donde el estudiante ya está inscrito
        $misInscripcionesIds = $estudiante->inscripciones()->pluck('id_curso')->toArray();

        // Cursos donde el estudiante está en lista de espera
        $misListaEsperaIds = $estudiante->listaEspera()->pluck('id_curso')->toArray();

        return view('estudiante.matriculacion', compact(
            'estudiante',
            'cursos',
            'misInscripcionesIds',
            'misListaEsperaIds',
            'cuatrimestreVigente',
            'ventana',
            'formato',
            'totalEtapas'
        ));
    }

    /**
     * Procesar la matriculación
     */
    public function inscribir(Request $request)
    {
        $request->validate(['id_curso' => 'required|exists:curso,id_curso']);

        $estudiante = Estudiante::where('id_usuario', Auth::id())->firstOrFail();
        $curso = Curso::findOrFail($request->id_curso);

        try {
            $resultado = $this->inscripcionService->inscribir($estudiante, $curso);

            // Si retorna una instancia de Lista_espera
            if ($resultado instanceof Lista_espera) {
                return redirect()->back()->with('info', 'El cupo está lleno. Has sido ingresado a la lista de espera');
            }

            return redirect()->back()->with('success', 'Te has matriculado exitosamente en el curso.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Procesar la desmatriculación
     */
    public function desinscribir(Request $request)
    {
        $request->validate(['id_curso' => 'required|exists:curso,id_curso']);

        $estudiante = Estudiante::where('id_usuario', Auth::id())->firstOrFail();
        $curso = Curso::findOrFail($request->id_curso);

        try {
            $this->inscripcionService->desinscribir($estudiante, $curso);

            return redirect()->back()->with('success', 'Has retirado la asignatura correctamente.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Procesar el retiro voluntario de la lista de espera
     */
    public function quitarListaEspera(Request $request)
    {
        $request->validate(['id_curso' => 'required|exists:curso,id_curso']);

        $estudiante = Estudiante::where('id_usuario', Auth::id())->firstOrFail();
        $curso = Curso::findOrFail($request->id_curso);

        try {
            $this->inscripcionService->quitarDeListaEspera($estudiante, $curso);

            return redirect()->back()->with('success', 'Has salido de la lista de espera de la asignatura.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

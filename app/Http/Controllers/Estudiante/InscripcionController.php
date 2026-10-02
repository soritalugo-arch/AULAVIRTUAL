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

        // Cuatrimestre del plan en el que va el estudiante: solo las materias
        // de esa etapa le corresponden en esta inscripción. Quien completó el
        // plan (null) no tiene materias que inscribir.
        $etapaActual = $this->historial->etapaActual($estudiante);

        $totalEtapas = $carrera
            ? $carrera->cursos()->withPivot('etapa')->get()
                ->pluck('pivot.etapa')
                ->filter()
                ->max()
            : null;

        // Oferta académica: solo las materias de la etapa del plan y del
        // cuatrimestre vigente, por la carrera del estudiante
        $cursos = $carrera && $cuatrimestreVigente && $etapaActual !== null
            ? Curso::with(['horarios', 'profesores'])
                ->withCount('inscripciones')
                ->whereHas('carreras', fn ($q) => $q
                    ->whereKey($carrera->id_carrera)
                    ->where('curso_carrera.etapa', $etapaActual))
                ->whereHas('cuatrimestres', fn ($q) => $q->whereKey($cuatrimestreVigente->id_cuatrimestre))
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
            'etapaActual',
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

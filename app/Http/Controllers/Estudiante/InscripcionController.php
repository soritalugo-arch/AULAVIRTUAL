<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Services\InscripcionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class InscripcionController extends Controller
{
    protected InscripcionService $inscripcionService;

    public function __construct(InscripcionService $inscripcionService)
    {
        $this->inscripcionService = $inscripcionService;
    }

    /**
     * Muestra la vista de matriculación / desmatriculación con las asignaturas disponibles.
     */
    public function index()
    {
        // Obtener el estudiante autenticado
        $estudiante = Estudiante::where('id_usuario', Auth::id())->firstOrFail();

        // Cursos disponibles
        $cursos = Curso::with(['horarios', 'profesores'])->get();

        // Cursos donde el estudiante ya está inscrito
        $misInscripcionesIds = $estudiante->inscripciones()->pluck('id_curso')->toArray();

        return view('estudiante.matriculacion', compact('estudiante', 'cursos', 'misInscripcionesIds'));
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
            if ($resultado instanceof \App\Models\Lista_espera) {
                return redirect()->back()->with('info', 'El cupo estaba lleno. Has sido ingresado a la lista de espera.');
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
}

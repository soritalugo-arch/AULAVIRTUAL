<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Curso;
use App\Services\ReporteService; // Importante: Agregar el servicio para traer los cuatrimestres

class AsignacionController extends Controller
{
    public function index(Request $request, ReporteService $reportes)
    {
        // 1. Obtener los datos del cuatrimestre que exige el layout admin.blade.php
        $cuatrimestres = $reportes->cuatrimestres();
        $idCuatrimestre = $request->filled('cuatrimestre')
            ? ($cuatrimestres->firstWhere('id_cuatrimestre', $request->integer('cuatrimestre'))?->id_cuatrimestre ?? abort(404))
            : $this->cuatrimestrePorDefecto($cuatrimestres);

        // 2. Tu lógica original de asignaciones
        $cursos = DB::table('curso')->get();
        
        $profesores = DB::table('profesor')
            ->join('usuario', 'profesor.id_usuario', '=', 'usuario.id_usuario')
            ->select('profesor.id_usuario', 'usuario.nombres', 'usuario.apellidos')
            ->get(); 
        
        // 3. Enviar todo a la vista
        return view('admin.Asignacion_prof', compact('cuatrimestres', 'idCuatrimestre', 'cursos', 'profesores'));
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'curso_id'    => 'required|exists:curso,id_curso',
            'profesor_id' => 'required|exists:profesor,id_usuario',
        ]);

        $cursoId = $request->curso_id;
        $profesorId = $request->profesor_id;

        // 1. Verificamos si ya está asignado a ese mismo curso
        $yaAsignado = DB::table('curso_profesor')
            ->where('curso_id', $cursoId)
            ->where('profesor_id', $profesorId)
            ->exists();

        if ($yaAsignado) {
            return back()->withErrors(['error_horario' => 'El profesor ya está asignado a este curso.'])->withInput();
        }

        // 2. Obtenemos los bloques de horario del curso que queremos asignar
        $horariosNuevoCurso = DB::table('horario')->where('id_curso', $cursoId)->get();

        if ($horariosNuevoCurso->isEmpty()) {
            return back()->withErrors(['error_horario' => 'El curso seleccionado aún no tiene horarios definidos en la base de datos.'])->withInput();
        }

        // 3. Validamos cada bloque de horario contra los cursos que ya dicta el profesor
        foreach ($horariosNuevoCurso as $horarioNuevo) {
            $choque = DB::table('curso_profesor')
                ->join('horario', 'curso_profesor.curso_id', '=', 'horario.id_curso')
                ->join('curso', 'curso_profesor.curso_id', '=', 'curso.id_curso')
                ->where('curso_profesor.profesor_id', $profesorId)
                ->where('horario.dia_semana', $horarioNuevo->dia_semana)
                ->where('horario.hora_inicio', '<', $horarioNuevo->hora_fin)
                ->where('horario.hora_fin', '>', $horarioNuevo->hora_inicio)
                ->select('curso.nombre', 'horario.hora_inicio', 'horario.hora_fin', 'horario.dia_semana')
                ->first();

            // Si encuentra un choque en cualquier día, bloquea la asignación
            if ($choque) {
                return back()->withErrors([
                    'error_horario' => "Choque detectado el día {$choque->dia_semana}. El profesor ya dicta el curso '{$choque->nombre}' en el horario de {$choque->hora_inicio} a {$choque->hora_fin}."
                ])->withInput();
            }
        }

        // 4. Si pasa todas las validaciones, guardamos en la tabla pivote
        DB::table('curso_profesor')->insert([
            'curso_id' => $cursoId,
            'profesor_id' => $profesorId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Profesor asignado correctamente al curso.');} 

    /**
     * Método auxiliar para calcular el cuatrimestre actual si no hay ninguno en la URL
     */
    private function cuatrimestrePorDefecto($cuatrimestres): int
    {
        $vigente = $cuatrimestres->first(
            fn ($c) => $c->fecha_inicio->lte(today()) && $c->fecha_fin->gte(today())
        );

        $empezado = $cuatrimestres->first(fn ($c) => $c->fecha_inicio->lte(today()));

        return ($vigente ?? $empezado ?? $cuatrimestres->first())->id_cuatrimestre;
    }

    /**
     * Obtiene los horarios de un curso específico
     */
    /**
     * Obtiene los horarios de un curso específico
     */
    public function getHorariosPorCurso($id)
    {
        // Buscamos directamente en la tabla horario usando el id del curso
        $horarios = DB::table('horario')->where('id_curso', $id)->get();

        if ($horarios->isEmpty()) {
            return response()->json([]);
        }

        // Formateamos la respuesta para devolver un array simple al frontend
        // Ajusto 'dia_semana' basado en cómo lo llamaste en el método store()
        $horariosFormateados = $horarios->map(function($horario) {
            return 'Día ' . $horario->dia_semana . ': ' . $horario->hora_inicio . ' - ' . $horario->hora_fin;
        });

        return response()->json($horariosFormateados);
    }

    /**
     * Obtiene las materias ya asignadas a un profesor
     */
   /**
     * Obtiene las materias ya asignadas a un profesor (Actualizado)
     */
    public function getMateriasPorProfesor($id)
    {
        $materias = DB::table('curso_profesor')
            ->join('curso', 'curso.id_curso', '=', 'curso_profesor.curso_id')
            ->where('curso_profesor.profesor_id', $id)
            ->select('curso.id_curso', 'curso.nombre') // Modificado: Ahora pedimos el ID y el nombre
            ->get();

        return response()->json($materias);
    }

    /**
     * Elimina la asignación de un profesor a un curso
     */
    public function eliminarAsignacion(Request $request)
    {
        DB::table('curso_profesor')
            ->where('curso_id', $request->curso_id)
            ->where('profesor_id', $request->profesor_id)
            ->delete();

        return response()->json(['success' => true]);
    }
}
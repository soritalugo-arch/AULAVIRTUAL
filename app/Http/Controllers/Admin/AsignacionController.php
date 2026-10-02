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

        // 2. Modificado: Cursos verificando si tienen profesor
        $cursos = DB::table('curso')
            ->leftJoin('curso_profesor', 'curso.id_curso', '=', 'curso_profesor.curso_id')
            ->select('curso.id_curso', 'curso.nombre', DB::raw('COUNT(curso_profesor.profesor_id) as asignado'))
            ->groupBy('curso.id_curso', 'curso.nombre')
            ->orderBy('curso.nombre', 'asc') // Orden alfabético para mejor vista
            ->get();
        
        // 3. Modificado: Profesores verificando cuántas materias tienen
        $profesores = DB::table('profesor')
            ->join('usuario', 'profesor.id_usuario', '=', 'usuario.id_usuario')
            ->leftJoin('curso_profesor', 'profesor.id_usuario', '=', 'curso_profesor.profesor_id')
            ->select('profesor.id_usuario', 'usuario.nombres', 'usuario.apellidos', DB::raw('COUNT(curso_profesor.curso_id) as cant_materias'))
            ->groupBy('profesor.id_usuario', 'usuario.nombres', 'usuario.apellidos')
            ->orderBy('usuario.nombres', 'asc') // Orden alfabético
            ->get(); 
        
        // 4. Enviar todo a la vista
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

        // 1. Validamos que el curso tenga horarios
        $horariosNuevoCurso = DB::table('horario')->where('id_curso', $cursoId)->get();

        if ($horariosNuevoCurso->isEmpty()) {
            return back()->withErrors([
                'error_horario' => 'El curso seleccionado aún no tiene horarios definidos en la base de datos.'
            ])->withInput();
        }

        // 2. Validamos cada bloque de horario contra los cursos que YA dicta el NUEVO profesor
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

            // Si encuentra un choque en cualquier día para el NUEVO profesor, bloquea
            if ($choque) {
                return back()->withErrors([
                    'error_horario' => "Choque detectado el día {$choque->dia_semana}. El nuevo profesor ya dicta el curso '{$choque->nombre}' en el horario de {$choque->hora_inicio} a {$choque->hora_fin}."
                ])->withInput();
            }
        }

        // 3. Si pasa la validación (no hay choques), reasignamos usando una Transacción
        DB::transaction(function () use ($cursoId, $profesorId) {
            // A. Eliminamos CUALQUIER asignación previa que tenga este curso (desvincula al profe viejo)
            DB::table('curso_profesor')->where('curso_id', $cursoId)->delete();

            // B. Insertamos al nuevo profesor
            DB::table('curso_profesor')->insert([
                'curso_id' => $cursoId,
                'profesor_id' => $profesorId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return redirect()->back()->with('success', 'Profesor asignado correctamente. Si la materia pertenecía a otro docente, ha sido reasignada con éxito.');
    }

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
     * Obtiene los horarios y el profesor actual de un curso específico
     */
    public function getHorariosPorCurso($id)
    {
        // Buscamos los horarios
        $horarios = DB::table('horario')->where('id_curso', $id)->get();
        
        // Buscamos al profesor que actualmente tiene asignado este curso
        $profesorActual = DB::table('curso_profesor')
            ->join('usuario', 'curso_profesor.profesor_id', '=', 'usuario.id_usuario')
            ->where('curso_profesor.curso_id', $id)
            ->select('usuario.nombres', 'usuario.apellidos')
            ->first();

        $horariosFormateados = $horarios->map(function($horario) {
            return 'Día ' . $horario->dia_semana . ': ' . $horario->hora_inicio . ' - ' . $horario->hora_fin;
        });

        // Devolvemos ambos datos en formato JSON
        return response()->json([
            'horarios' => $horariosFormateados,
            'profesor_actual' => $profesorActual
        ]);
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
<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Cuatrimestre;
use App\Models\Inscripcion;
use App\Mail\CursoPorComenzar;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

#[Signature('app:notificar-cursos-por-comenzar')]
#[Description('Command description')]
class NotificarCursosPorComenzar extends Command
{
    protected $signature = 'notificar:cursos';
    protected $description = 'Envía email a estudiantes de cursos que empiezan en 3 días';

    public function handle()
    {
        // Buscar el cuatrimestre que inicie exactamente en 3 días
        $fechaObjetivo = Carbon::today()->addDays(3)->toDateString();
        
        $cuatrimestres = Cuatrimestre::where('fecha_inicio', $fechaObjetivo)->get();

        foreach ($cuatrimestres as $cuatrimestre) {
            // Obtener todas las inscripciones (asumiendo que las relaciones están configuradas)
            // Ajusta esta consulta según la estructura exacta de tus relaciones
            $inscripciones = Inscripcion::with(['estudiante', 'curso'])
                ->whereHas('curso.cuatrimestres', function($q) use ($cuatrimestre) {
                    $q->where('id_cuatrimestre', $cuatrimestre->id_cuatrimestre);
                })->get();

            foreach ($inscripciones as $inscripcion) {
                Mail::to($inscripcion->estudiante->email)->send(
                    new CursoPorComenzar(
                        $inscripcion->estudiante->nombres,
                        $inscripcion->curso->nombre,
                        $cuatrimestre->fecha_inicio
                    )
                );
            }
        }

        $this->info('Notificaciones enviadas con éxito.');
    }
}

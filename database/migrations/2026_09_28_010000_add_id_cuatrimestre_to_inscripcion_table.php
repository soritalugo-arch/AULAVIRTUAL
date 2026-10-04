<?php

use App\Models\Cuatrimestre;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La inscripcion queda ligada a un cuatrimestre, igual que calificacion y
     * asistencia, para que los reportes por periodo sean consultables.
     *
     * Las inscripciones existentes son todas del cuatrimestre vigente (q2):
     * se registraron del 1 al 6 de septiembre de 2026, justo antes de que ese
     * cuatrimestre arrancara el 7. La regla del proceso es que la matricula
     * precede al inicio del periodo, asi que a cada inscripcion se le asigna
     * el primer cuatrimestre cuya fecha_inicio es posterior a su fecha de
     * inscripcion.
     */
    public function up(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cuatrimestre')
                ->nullable()
                ->after('id_curso');

            $table->foreign('id_cuatrimestre')
                ->references('id_cuatrimestre')
                ->on('cuatrimestre')
                ->onDelete('cascade');
        });

        $this->poblar();

        $huerfanas = DB::table('inscripcion')->whereNull('id_cuatrimestre')->count();

        if ($huerfanas > 0) {
            throw new RuntimeException(
                "No se pudo asignar cuatrimestre a {$huerfanas} inscripciones; "
                .'no existe ningun cuatrimestre posterior a su fecha de inscripcion.'
            );
        }
    }

    /**
     * Asigna a cada inscripcion el primer cuatrimestre que empieza despues
     * de su fecha de inscripcion. Se hace en PHP y no con UPDATE ... FROM para
     * que la migracion corra igual en PostgreSQL y en SQLite (los tests).
     */
    private function poblar(): void
    {
        $cuatrimestres = Cuatrimestre::orderBy('fecha_inicio')
            ->get(['id_cuatrimestre', 'fecha_inicio']);

        DB::table('inscripcion')
            ->select('id_inscripcion', 'fecha_inscripcion')
            ->orderBy('id_inscripcion')
            ->chunk(500, function ($inscripciones) use ($cuatrimestres) {
                foreach ($inscripciones as $inscripcion) {
                    $periodo = $cuatrimestres
                        ->firstWhere('fecha_inicio', '>', $inscripcion->fecha_inscripcion);

                    if ($periodo) {
                        DB::table('inscripcion')
                            ->where('id_inscripcion', $inscripcion->id_inscripcion)
                            ->update(['id_cuatrimestre' => $periodo->id_cuatrimestre]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            $table->dropForeign(['id_cuatrimestre']);
            $table->dropColumn('id_cuatrimestre');
        });
    }
};

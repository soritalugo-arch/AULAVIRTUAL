<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite repetir una asignatura reprobada.
     *
     * La unicidad antigua pasaba por (id_estudiante, id_curso) y prohibia
     * inscribirse dos veces al mismo curso en TODA la carrera, incluso en
     * cuatrimestres distintos. La regla de la institucion es que reprobar no
     * elimina la materia: el estudiante puede volver a cursarla en otro
     * periodo, y el historial debe mostrarlo como "repetida".
     *
     * La unicidad nueva pasa a (id_estudiante, id_curso, id_cuatrimestre):
     * sigue prohibido inscribirse dos veces en el MISMO periodo, pero queda
     * permitido volver a cursar la asignatura en otro cuatrimestre.
     */
    public function up(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            $table->dropUnique(['id_estudiante', 'id_curso']);

            $table->unique(['id_estudiante', 'id_curso', 'id_cuatrimestre']);
        });
    }

    public function down(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            $table->dropUnique(['id_estudiante', 'id_curso', 'id_cuatrimestre']);

            $table->unique(['id_estudiante', 'id_curso']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La etapa (1, 2, 3...) de cada materia dentro del plan de estudios de la
 * carrera: el cuatrimestre del recorrido, distinto del calendario Q01/Q02.
 *
 * Antes todas las materias de una carrera eran iguales; con esta columna el
 * pensum queda ordenado y cada carrera puede mostrar su recorrido de 5 o 6
 * cuatrimestres de 2 a 3 materias cada uno.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curso_carrera', function (Blueprint $table) {
            $table->unsignedTinyInteger('etapa')->default(1)->after('carrera_id');
        });
    }

    public function down(): void
    {
        Schema::table('curso_carrera', function (Blueprint $table) {
            $table->dropColumn('etapa');
        });
    }
};
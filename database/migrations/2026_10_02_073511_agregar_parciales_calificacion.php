<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cuatro evaluaciones parciales del 25 % con promedio automatico.
 *
 * Lo nuevo son las cuatro parciales y el "promedio" que sale de ellas (opcion A:
 * una parcial que aun no se captura cuenta como cero, asi que el promedio sube
 * a medida que el profesor carga). "nota" no se borra: pasa a nullable y queda
 * sincronizada con el promedio redondeado, de modo que cualquier consulta que
 * la lea siga devolviendo un numero.
 *
 * LAS CALIFICACIONES QUE YA EXISTIAN NO SE TOCAN. Se quedan con su nota final y
 * con tiene_parciales = false: inventarles cuatro parciales falsearia el
 * historial. Siguen leyendose igual que antes (Calificacion::notaEfectiva()
 * devuelve la nota cuando la fila no tiene parciales) y en cuanto el profesor
 * cargue una parcial la fila pasa al regimen nuevo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('calificacion', 'parcial1')) {
            Schema::table('calificacion', function (Blueprint $table) {
                $table->decimal('parcial1', 4, 1)->nullable();
                $table->decimal('parcial2', 4, 1)->nullable();
                $table->decimal('parcial3', 4, 1)->nullable();
                $table->decimal('parcial4', 4, 1)->nullable();
                $table->decimal('promedio', 4, 2)->nullable();
                $table->boolean('tiene_parciales')->default(false);
            });
        }

        // "nota" pasa a nullable: con parciales la definitiva sale del promedio y
        // todavia no existe al cargar la primera parcial. Las filas viejas la
        // siguen usando, asi que no se pierde nada.
        Schema::table('calificacion', function (Blueprint $table) {
            $table->integer('nota')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Antes de quitar las columnas, la definitiva vuelve a "nota": si no, las
        // medias calificaciones (que solo tienen promedio) quedarian sin nota.
        DB::table('calificacion')
            ->whereNull('nota')
            ->whereNotNull('promedio')
            ->update(['nota' => DB::raw('ROUND(promedio)')]);

        Schema::table('calificacion', function (Blueprint $table) {
            $table->dropColumn([
                'parcial1',
                'parcial2',
                'parcial3',
                'parcial4',
                'promedio',
                'tiene_parciales',
            ]);
        });
    }
};

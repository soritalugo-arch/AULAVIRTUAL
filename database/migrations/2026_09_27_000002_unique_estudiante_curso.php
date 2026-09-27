<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Evita que un estudiante se inscriba dos veces en el mismo curso
     * y que aparezca varias veces en la lista de espera del mismo curso.
     */
    public function up(): void
    {
        // Limpiar duplicados existentes (conservar el registro más antiguo)
        $this->deduplicar('inscripcion', 'id_inscripcion', ['id_estudiante', 'id_curso']);
        $this->deduplicar('lista_espera', 'idlista_espera', ['id_estudiante', 'id_curso']);

        Schema::table('inscripcion', function (Blueprint $table) {
            $table->unique(['id_estudiante', 'id_curso']);
        });

        Schema::table('lista_espera', function (Blueprint $table) {
            $table->unique(['id_estudiante', 'id_curso']);
        });
    }

    public function down(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            $table->dropUnique(['id_estudiante', 'id_curso']);
        });

        Schema::table('lista_espera', function (Blueprint $table) {
            $table->dropUnique(['id_estudiante', 'id_curso']);
        });
    }

    /**
     * Elimina las filas duplicadas de una tabla dejando solo la más antigua.
     */
    private function deduplicar(string $tabla, string $pk, array $columnas): void
    {
        $duplicados = DB::table($tabla)
            ->select(array_merge($columnas, [DB::raw("MIN($pk) as min_id")]))
            ->groupBy($columnas)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicados as $fila) {
            DB::table($tabla)
                ->where($columnas[0], $fila->{$columnas[0]})
                ->where($columnas[1], $fila->{$columnas[1]})
                ->where($pk, '!=', $fila->min_id)
                ->delete();
        }
    }
};
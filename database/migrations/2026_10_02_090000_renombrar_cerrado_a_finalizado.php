<?php

use App\Models\Cuatrimestre;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El estado "cerrado" pasa a llamarse "finalizado".
 *
 * "Cerrado" se confundía con "matrícula cerrada", que es otra cosa: el período
 * no se cierra la matrícula, se termina. Además aparece un cuarto estado,
 * "pre_matricula", que es el período nuevo que aparece solo, todavía sin
 * matrícula, para que la rectora vaya armando la oferta del período siguiente.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cuatrimestre')
            ->where('estado', 'cerrado')
            ->update(['estado' => Cuatrimestre::ESTADO_FINALIZADO]);
    }

    public function down(): void
    {
        DB::table('cuatrimestre')
            ->where('estado', Cuatrimestre::ESTADO_FINALIZADO)
            ->update(['estado' => 'cerrado']);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada cuatrimestre del calendario pasa por tres estados que separan los
     * dos momentos del período: "matriculacion" (solo se inscribe), "en_curso"
     * (las clases corren: se califica y se registra asistencia) y "cerrado"
     * (histórico, solo lectura). La rectora cambia el estado desde su panel.
     *
     * El valor por defecto es "matriculacion" para que las pruebas y una base
     * recién creada arranquen con la matrícula abierta.
     */
    public function up(): void
    {
        Schema::table('cuatrimestre', function (Blueprint $table) {
            $table->string('estado', 20)
                ->default('matriculacion')
                ->after('fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::table('cuatrimestre', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
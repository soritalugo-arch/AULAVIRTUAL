<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la relación estudiante -> carrera para poder filtrar
     * la oferta académica del módulo de matriculación.
     */
    public function up(): void
    {
        Schema::table('estudiante', function (Blueprint $table) {
            $table->unsignedBigInteger('id_carrera')->nullable();
            $table->foreign('id_carrera')
                ->references('id_carrera')
                ->on('carrera')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('estudiante', function (Blueprint $table) {
            $table->dropForeign(['id_carrera']);
            $table->dropColumn('id_carrera');
        });
    }
};
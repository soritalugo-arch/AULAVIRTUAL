<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asistencia', function (Blueprint $table) {
            $table->id('id_asistencia');
            $table->foreignId('id_estudiante')->constrained('estudiante', 'id_usuario')->onDelete('cascade');
            $table->foreignId('id_curso')->constrained('curso', 'id_curso')->onDelete('cascade');
            $table->foreignId('id_cuatrimestre')->constrained('cuatrimestre', 'id_cuatrimestre')->onDelete('cascade');
            $table->foreign(['id_curso','id_cuatrimestre'])->onDelete('cascade')->references(['curso_id','cuatrimestre_id'])->on('curso_cuatrimestre');
            $table->date('fecha');
            $table->boolean('presente'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

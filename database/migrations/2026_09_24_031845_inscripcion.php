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
        Schema::create('inscripcion', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->foreignId('id_estudiante')->constrained('estudiante', 'id_usuario')->onDelete('cascade');
            $table->foreignId('id_curso')->constrained('curso', 'id_curso')->onDelete('cascade');
            $table->date('fecha_inscripcion');
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

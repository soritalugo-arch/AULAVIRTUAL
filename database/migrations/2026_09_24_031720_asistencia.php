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
        Shema::create('asistencia', function (Blueprint $table) {
            $table->id('id_asistencia');
            $table->foreignId('id_estudiante')->constrained('estudiante')->onDelete('cascade');
            $table->foreignId('id_curso')->constrained('curso')->onDelete('cascade');
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

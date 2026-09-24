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
        Schema::create('curso_profesor', function (Blueprint $table) {
            $table->unsignedBigInteger('curso_id');
            $table->unsignedBigInteger('profesor_id');
            $table->timestamps();

             // Definir las claves foráneas
            $table->foreign('curso_id')->references('id_curso')->on('curso')->onDelete('cascade');
            $table->foreign('profesor_id')->references('id_usuario')->on('profesor')->onDelete('cascade');
            $table->primary(['curso_id', 'profesor_id']); // Definir la clave primaria compuesta
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

<?php

namespace Database\Seeders;

use App\Models\Carrera;
use Illuminate\Database\Seeder;

class CarreraSeeder extends Seeder
{
    public const CARRERAS = [
        ['nombre' => 'Informática', 'duracion' => 6],
        ['nombre' => 'Administración', 'duracion' => 6],
        ['nombre' => 'Contaduría', 'duracion' => 6],
        ['nombre' => 'Diseño Gráfico', 'duracion' => 5],
        ['nombre' => 'Marketing Digital', 'duracion' => 5],
        ['nombre' => 'Turismo', 'duracion' => 5],
        ['nombre' => 'Enfermería', 'duracion' => 6],
        ['nombre' => 'Electrónica', 'duracion' => 6],
    ];

    public function run(): void
    {
        foreach (self::CARRERAS as $carrera) {
            Carrera::create($carrera);
        }
    }
}
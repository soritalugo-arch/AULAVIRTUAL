<?php

namespace Database\Seeders;

use App\Models\Carrera;
use Illuminate\Database\Seeder;

class CarreraSeeder extends Seeder
{
    public const CARRERAS = [
        ['nombre' => 'Informática', 'duracion' => 5],
        ['nombre' => 'Administración', 'duracion' => 5],
        ['nombre' => 'Contaduría', 'duracion' => 5],
        ['nombre' => 'Diseño Gráfico', 'duracion' => 5],
        ['nombre' => 'Marketing Digital', 'duracion' => 5],
        ['nombre' => 'Turismo', 'duracion' => 5],
        ['nombre' => 'Enfermería', 'duracion' => 5],
        ['nombre' => 'Electrónica', 'duracion' => 5],
    ];

    public function run(): void
    {
        foreach (self::CARRERAS as $carrera) {
            Carrera::create($carrera);
        }
    }
}
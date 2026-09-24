<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estudiante>
 */
class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    public function definition(): array
    {
        $usuario = Usuario::factory()->create();

        return [
            'id_usuario' => $usuario->id_usuario,
            'fecha_nacimiento' => fake()->dateTimeBetween('-26 years', '-17 years')->format('Y-m-d'),
            'cedula' => fake()->unique()->numerify('2#######'),
            'deuda' => false,
        ];
    }
}
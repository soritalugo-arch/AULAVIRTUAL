<?php

namespace Database\Factories;

use App\Models\Profesor;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profesor>
 */
class ProfesorFactory extends Factory
{
    protected $model = Profesor::class;

    public function definition(): array
    {
        $usuario = Usuario::factory()->create();

        return [
            'id_usuario' => $usuario->id_usuario,
        ];
    }
}
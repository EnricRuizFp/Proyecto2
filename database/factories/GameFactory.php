<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User; // Necesitamos User para el created_by
use Illuminate\Support\Str; // Aunque el modelo lo genera, podemos poner uno aquí también

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // El campo 'code' se genera automáticamente en el modelo Game.php (método boot),
        // por lo que no es estrictamente necesario definirlo aquí, pero no hace daño.
        // Lo más importante es definir 'created_by' ya que no es nullable.
        return [
            'code' => Str::upper(Str::random(4)), // Opcional, el modelo lo sobrescribirá
            'creation_date' => $this->faker->dateTimeThisDecade(),
            'start_date' => null,
            'is_public' => $this->faker->boolean,
            'is_finished' => false,
            'end_date' => null,
            'winner' => null, // Puede ser null
            'points' => null, // Puede ser null
            'created_by' => User::factory(), // Crea un usuario nuevo para cada partida
        ];
    }
}

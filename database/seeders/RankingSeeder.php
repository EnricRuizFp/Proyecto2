<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ranking;
use App\Models\User;
use Faker\Factory as Faker;

class RankingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $userIds = User::pluck('id')->take(50)->toArray(); // Obtiene los primeros 50 usuarios únicos

        foreach ($userIds as $userId) {
            $wins = $faker->numberBetween(5, 30);   // Partidas ganadas (entre 5 y 30)
            $losses = $faker->numberBetween(5, 30); // Partidas perdidas (entre 5 y 30)
            $draws = $faker->numberBetween(0, 10);  // Partidas empatadas (entre 0 y 10)

            // Calcula los puntos en base a las reglas
            $points = 0;
            for ($i = 0; $i < $wins; $i++) {
                $points += $faker->numberBetween(3, 6);
            }
            for ($i = 0; $i < $losses; $i++) {
                $points -= $faker->numberBetween(3, 6);
            }
            // Empates no suman ni restan

            // Crea el ranking para el usuario
            Ranking::create([
                'user_id' => $userId,
                'wins' => $wins,
                'losses' => $losses,
                'draws' => $draws,
                'points' => max(0, $points) // Evita que los puntos sean negativos
            ]);
        }
    }
}

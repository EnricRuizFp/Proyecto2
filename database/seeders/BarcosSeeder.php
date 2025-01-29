<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barco;

class BarcosSeeder extends Seeder
{
    public function run()
    {
        $barcos = [
            ['nombre' => 'Destructor', 'tamaño' => 2],
            ['nombre' => 'Submarino', 'tamaño' => 3],
            ['nombre' => 'Crucero', 'tamaño' => 3],
            ['nombre' => 'Acorazado', 'tamaño' => 4],
            ['nombre' => 'Portaaviones', 'tamaño' => 5],
        ];

        foreach ($barcos as $barco) {
            Barco::create($barco);
        }
    }
}

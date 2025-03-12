<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ship;

class ShipSeeder extends Seeder
{
    public function run()
    {
        $ships = [
            ['name' => 'Portaaviones', 'size' => 5],
            ['name' => 'Acorazado', 'size' => 4],
            ['name' => 'Crucero', 'size' => 3],
            ['name' => 'Destructor', 'size' => 2],
        ];

        foreach ($ships as $ship) {
            Ship::create($ship);
        }
    }
}

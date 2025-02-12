<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ship;

class ShipSeeder extends Seeder
{
    public function run()
    {
        // Define each ship's data.
        $ships = [
            ['name' => 'acorazado','size' => 4],
            ['name' => 'crucero','size' => 3],
            ['name' => 'destructor','size' => 2],
            ['name' => 'fragata','size' => 1],
        ];

        // Create ships
        foreach ($ships as $ship) {
            Ship::create($ship);
        }
    }
}
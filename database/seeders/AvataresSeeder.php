<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Avatar;

class AvataresSeeder extends Seeder
{
    public function run()
    {
        $avatares = [
            ['nombre' => 'Avatar1', 'ruta_imagen' => 'avatars/avatar1.png'],
            ['nombre' => 'Avatar2', 'ruta_imagen' => 'avatars/avatar2.png'],
            ['nombre' => 'Avatar3', 'ruta_imagen' => 'avatars/avatar3.png'],
            // Añade más avatares según necesites
        ];

        foreach ($avatares as $avatar) {
            Avatar::create($avatar);
        }
    }
}

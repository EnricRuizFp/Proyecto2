<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Avatar;

class AvatarSeeder extends Seeder
{
    public function run()
    {
        // Definimos los datos de cada avatar.
        $avatars = [
            [
                'name' => 'Default',
                'file' => 'default.webp'
            ],
            [
                'name' => 'Avatar 1',
                'file' => 'avatar1.webp'
            ],
            [
                'name' => 'Avatar 2',
                'file' => 'avatar2.webp'
            ],
            [
                'name' => 'Avatar 3',
                'file' => 'avatar3.webp'
            ],
            [
                'name' => 'Avatar 4',
                'file' => 'avatar4.webp'
            ],
        ];

        foreach ($avatars as $avatarData) {
            // Creamos el registro del avatar en la tabla "avatars"
            $avatar = Avatar::create([
                'name' => $avatarData['name']
            ]);

            // Definimos la ruta al archivo original (asegúrate de que exista en esa ubicación)
            $path = database_path('seeders/avatars/' . $avatarData['file']);

            if (file_exists($path)) {
                // Adjuntamos la imagen a la colección "avatars" definida en el modelo
                $avatar->addMedia($path)
                    ->preservingOriginal() // Opcional: conserva el archivo original
                    ->toMediaCollection('avatars');
            } else {
                // En caso de no encontrar el archivo, se muestra una advertencia en consola
                $this->command->warn("El archivo {$avatarData['file']} no fue encontrado en {$path}");
            }
        }
    }
}
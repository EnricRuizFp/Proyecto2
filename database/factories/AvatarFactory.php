<?php

namespace Database\Factories;

use App\Models\Avatar;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvatarFactory extends Factory
{
    protected $model = Avatar::class;

    public function definition()
    {
        return [
            'name'       => $this->faker->word,
            'image_path' => 'avatars/' . $this->faker->image('public/storage/avatars', 640, 480, 'people', false),
        ];
    }
}

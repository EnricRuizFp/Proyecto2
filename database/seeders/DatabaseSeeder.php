<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(PermissionTableSeeder::class);

        // Creates Admin, User and 50 more random users
        $this->call(CreateAdminUserSeeder::class);
        $this->call(AvatarSeeder::class);
        $this->call(ShipSeeder::class);
        $this->call(RankingSeeder::class);

        // $this->call(RoleSeeder::class);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}

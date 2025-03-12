<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'id' => 1,
            'name' => 'David',
            'surname1' => 'Herrera',
            'username' => 'dherrera',
            'email' => 'admin@demo.com',
            'password' => bcrypt('12345678'),
            'nationality' => 'spain'
        ]);

        $admin_role = Role::create(['name' => 'admin']);
        $user_role = Role::create(['name' => 'user']);
        $permissions = [
            'post-list','post-create','post-edit','post-delete',
            'role-list','role-create','role-edit','role-delete',
            'permission-list','permission-create','permission-edit','permission-delete',
            'user-list','user-create','user-edit','user-delete',

            // Other permissions
            'avatar-list','avatar-create','avatar-edit','avatar-delete',
            'ship-list','ship-create','ship-edit','ship-delete',
            'game-list','game-create','game-edit','game-delete',
            'move-list','move-create','move-edit','move-delete',
            'ranking-list','ranking-create','ranking-edit','ranking-delete',
            'chat-list','chat-create','chat-edit','chat-delete',
            'user_avatar-list','user_avatar-create','user_avatar-edit', 'user_avatar-delete',

        ];
        $user_role->syncPermissions($permissions);

        $permissions = Permission::pluck('id','id')->all();

        $admin_role->syncPermissions($permissions);

        $user->assignRole([$admin_role->id]);

        $user = User::create([
            'id' => 2,
            'name' => 'User',
            'surname1' => 'User',
            'username' => 'user',
            'email' => 'user@demo.com',
            'password' => bcrypt('12345678'),
            'nationality' => 'portugal'
        ]);
        $user->assignRole([$user_role->id]);


        /* -- GENERATE LOCAL TEAM USERS -- */
        $user = User::create([
            'id' => 3,
            'name' => 'Daniel',
            'surname1' => 'Lobera',
            'surname2' => 'Simon',
            'username' => 'K4ze',
            'email' => 'danielloberafp@ibf.cat',
            'password' => bcrypt('Asdqwe!23'),
            'nationality' => 'spain'
        ]);
        $user->assignRole([$user_role->id]);

        $user = User::create([
            'id' => 4,
            'name' => 'Enric',
            'surname1' => 'Ruiz',
            'surname2' => 'Badia',
            'username' => 'ERB',
            'email' => 'enricruizfp@ibf.cat',
            'password' => bcrypt('Asdqwe!23'),
            'nationality' => 'spain'
        ]);
        $user->assignRole([$user_role->id]);

        /* -- GENERATE MASS USERS -- */

        $userQuantity = 50;
        $faker = Faker::create();

        $countries = [
            'Europe', 'America', 'Asia', 'Africa', 'Oceania'
        ];

        $usedUsernames = []; // Almacena usernames generados para evitar duplicados

        for ($i = 0; $i < $userQuantity; $i++) {
            $name = $faker->firstName;
            $surname1 = $faker->lastName;
            $surname2 = $faker->lastName;
            $baseUsername = strtolower(substr($name, 0, 1)) . ucfirst($surname1);
            $username = $baseUsername;
            $counter = 1;

            // Asegurar que el username sea único
            while (in_array($username, $usedUsernames) || User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $usedUsernames[] = $username; // Guardar el username para evitar duplicados

            $email = strtolower($username) . '@debattleship.com';
            $nationality = $faker->randomElement($countries);

            $user = User::create([
                'name' => $name,
                'surname1' => $surname1,
                'surname2' => $surname2,
                'username' => $username,
                'email' => $email,
                'password' => bcrypt('Asdqwe!23'),
                'nationality' => strtolower($nationality)
            ]);

            $user->assignRole([$user_role->id]);
        }

    }
}

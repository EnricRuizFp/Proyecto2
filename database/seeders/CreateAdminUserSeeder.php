<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

        $role = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'user']);
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

        ];
        $role2->syncPermissions($permissions);

        $permissions = Permission::pluck('id','id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);

        $user = User::create([
            'id' => 2,
            'name' => 'User',
            'surname1' => 'User',
            'username' => 'user',
            'email' => 'user@demo.com',
            'password' => bcrypt('12345678'),
            'nationality' => 'portugal'
        ]);
        $user->assignRole([$role2->id]);

    }
}

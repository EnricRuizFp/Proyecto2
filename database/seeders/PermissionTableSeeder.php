<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'post-list',
            'post-create',
            'post-edit',
            'post-all',
            'post-delete',

            // Other permissions
            'avatar-list','avatar-create','avatar-edit','avatar-delete',
            'ship-list','ship-create','ship-edit','ship-delete',
            'game-list','game-create','game-edit','game-delete',
            'move-list','move-create','move-edit','move-delete',
            'ranking-list','ranking-create','ranking-edit','ranking-delete',
            'chat-list','chat-create','chat-edit','chat-delete',
            
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserPermissionRole extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'user_admin',
        ]);
        $permission = [
            'create-song',
            'update-song',
            'delete-song',
            'view-song',
            'show-all-song',

            'create-category',
            'update-category',
            'delete-category',
            'view-category',
            'show-all-category',

            'create-artist',
            'update-artist',
            'delete-artist',
            'view-artist',
            'show-all-artist',

            'import-file',
        ];
        foreach ($permission as $per) {
            Permission::firstOrCreate(['name' => $per]);
        }

        $role->syncPermissions($permission);

    }
}

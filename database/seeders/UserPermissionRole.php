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
            'show-all-category', // eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2F1dGgvcmVnaXN0ZXIiLCJpYXQiOjE3NzQ4ODI3MjUsImV4cCI6MTc3NjA5MjMyNSwibmJmIjoxNzc0ODgyNzI1LCJqdGkiOiI5bnNnTnBZNEo1dlZucThEIiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.fn8PuMwRRo_DXkH5bD6rjGy1X4bCU07yGdbufyRCrvE

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

    }// eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2F1dGgvcmVnaXN0ZXIiLCJpYXQiOjE3NzQ4ODM5OTEsImV4cCI6MTc3NjA5MzU5MSwibmJmIjoxNzc0ODgzOTkxLCJqdGkiOiI1RUxKMzRKSWszNFlER0hOIiwic3ViIjoiNzAyIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.Ch9YA6blMsgyEajmPgc2BoIXmjCmQPce3am2eyyTeHY
}

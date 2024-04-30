<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'model' => 'Role', 'description' => 'Can add roles', 'added_by' => 1],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'model' => 'Role', 'description' => 'Can edit roles', 'added_by' => 1],
            ['name' => 'View Roles', 'slug' => 'roles.view', 'model' => 'Role', 'description' => 'Can view roles', 'added_by' => 1],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'model' => 'Role', 'description' => 'Can delete roles', 'added_by' => 1],
            ['name' => 'Active/Inactive Roles', 'slug' => 'roles.activeinactive', 'model' => 'Role', 'description' => 'Can Activate or deactivate roles', 'added_by' => 1],

            // ['name' => 'Create Users', 'slug' => 'users.create', 'model' => 'User', 'description' => 'Can add Users', 'added_by' => 1],
            // ['name' => 'Edit Users', 'slug' => 'users.edit', 'model' => 'User', 'description' => 'Can Edit Users', 'added_by' => 1],
            // ['name' => 'View Users', 'slug' => 'users.view', 'model' => 'User', 'description' => 'Can view Users', 'added_by' => 1],
            // ['name' => 'Delete Users', 'slug' => 'users.delete', 'model' => 'User', 'description' => 'Can delete Users', 'added_by' => 1],
            // ['name' => 'Active/Inactive Users', 'slug' => 'users.activeinactive', 'model' => 'User', 'description' => 'Can Activate or deactivate Users', 'added_by' => 1],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate($permission);
        }
    }
}

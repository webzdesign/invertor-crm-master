<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionRole;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = Permission::select('id')->pluck('id')->toArray();

        foreach ($permissions as $permission) {
            PermissionRole::updateOrCreate(['role_id' => 1, 'permission_id' => $permission]);
        }
    }
}

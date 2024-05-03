<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Admin',
                'added_by'  => 1
            ],
            [
                'name' => 'Seller',
                'slug' => 'seller',
                'description' => 'Seller',
                'added_by'  => 1
            ],
            [
                'name' => 'Driver',
                'slug' => 'driver',
                'description' => 'Driver',
                'added_by'  => 1
            ],
            [
                'name' => 'Supplier',
                'slug' => 'supplier',
                'description' => 'Supplier',
                'added_by'  => 1
            ]
        ];

        foreach ($roles as $role) {
            \App\Models\Role::updateOrCreate(['name' => $role['name']], $role);
        }

        \App\Models\UserRole::updateOrCreate(['user_id' => 1, 'role_id' => 1]);
    }
}

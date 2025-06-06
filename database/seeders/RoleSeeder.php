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
        /** change in user model if slug is changed for admin, seller driver and seller manager **/
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
            ],
            [
                'name' => 'Operative Director',
                'slug' => 'operative-director',
                'description' => 'operative director',
                'added_by'  => 1
            ],
            [
                'name' => 'Seller Manager',
                'slug' => 'seller-manager',
                'description' => 'seller manager',
                'added_by'  => 1
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Customer',
                'added_by'  => 1
            ]
        ];

        foreach ($roles as $role) {
            \App\Models\Role::updateOrCreate(['name' => $role['name']], $role);
        }

        \App\Models\UserRole::updateOrCreate(['user_id' => 1, 'role_id' => 1]);
    }
}

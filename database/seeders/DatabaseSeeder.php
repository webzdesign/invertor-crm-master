<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            UserSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            CountryStateCitySeeder::class,
            RolePermissionSeeder::class,
            SalesOrderStatusSeeder::class,
            CommissionSeeder::class
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ["phone" => 9999999999, "email" => "admin@gmail.com"],
            [
                'name' => "Admin",
                'email' => "ebike@gmail.com",
                'phone' => 9999999999,
                'email_verified_at' => now(),
                'password' => bcrypt('S0g|z>1x/6>v'),
                'added_by' => 0
            ]
        );
    }
}

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
            ["email" => "ebike@gmail.com"],
            [
                'name' => "Admin",
                'email' => "ebike@gmail.com",
                'email_verified_at' => now(),
                'password' => bcrypt('S0g|z>1x/6>v'),
                'added_by' => 0
            ]
        );
    }
}

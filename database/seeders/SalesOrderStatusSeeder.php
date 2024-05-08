<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalesOrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['New', 'No answer 1', 'No answer 2', 'No answer 3', 'sold', 'cancelled', 'scammer'] as $status) {
            \App\Models\SalesOrderStatus::updateOrCreate(['name' => $status, 'slug' => Str::slug($status)],['name' => $status, 'slug' => Str::slug($status)]);
        }
    }
}

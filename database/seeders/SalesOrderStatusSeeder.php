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
        $colors = ['#a9ebfc', '#ffff1f', '#99ccff', '#ff1f1f', '#00ff4c', '#001cff', '#00b3ff', '#dd2d20', '#3dcc00'];
        foreach (['NEW', 'NO ANSWERED 1', 'NO ANSWERED 2', 'CONFIRMED ORDER', 'CANCELLED', 'SCAMMER', 'AGREED TO BUY', 'CLOSED LOSS', 'CLOSED WIN'] as $key => $status) {
            \App\Models\SalesOrderStatus::updateOrCreate(['name' => $status, 'slug' => Str::slug($status)],['name' => $status, 'slug' => Str::slug($status), 'sequence' => $key, 'color' => $colors[$key]]);
        }
    }
}

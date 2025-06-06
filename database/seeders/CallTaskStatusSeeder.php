<?php

namespace Database\Seeders;

use App\Models\CallTaskStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CallTaskStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'id' => 1,
                'color' => '#a9ebfc',
                'name' => 'Call The New Client',
                'sequence' => 0,
                'type' => 1,
                'is_static' => 1
            ],
            [
                'id' => 2,
                'color' => '#bf4ec3',
                'name' => 'New Client',
                'sequence' => 1,
                'type' => 1,
                'is_static' => 1
            ],
            [
                'id' => 3,
                'color' => '#128f92',
                'name' => 'NO ANSWERED',
                'sequence' => 2,
                'type' => 1,
                'is_static' => 0
            ],
            [
                'id' => 4,
                'color' => '#c1c1c1',
                'name' => 'Success',
                'sequence' => 3,
                'type' => 1,
                'is_static' => 0
            ]
        ];

        foreach ($statuses as $status) {
            CallTaskStatus::updateOrCreate(['id' => $status['id']], [
                'name' => $status['name'],
                'slug' => Str::slug($status['name']),
                'sequence' => $status['sequence'],
                'color' => $status['color'],
                'type' => $status['type'],
                'is_static' => $status['is_static']
            ]);
        }
        $ids = CallTaskStatus::pluck('id')->toArray();
        if(!empty($ids)) {
            \App\Models\Role::where('slug','admin')->update(['filter_status'=>implode(',',$ids)]);
        }
    }
}

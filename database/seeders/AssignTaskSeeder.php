<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tasks')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'asset_id' => 2,
                'floor_id' => 1,
                'description' => 'jadi tak',
                'notes' => 'test',
                'status' => 'completed',
                'created_at' => '2025-11-25 03:13:17',
                'updated_at' => '2025-11-26 09:18:24',
            ],
            [
                'id' => 3,
                'user_id' => 2,
                'asset_id' => 2,
                'floor_id' => 1,
                'description' => 'test 2',
                'notes' => 'mesti knejadi',
                'status' => 'in progress',
                'created_at' => '2025-11-26 09:05:04',
                'updated_at' => '2025-11-26 09:18:03',
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'asset_id' => 3,
                'floor_id' => 2,
                'description' => 'testt3',
                'notes' => 'jadi tak',
                'status' => 'pending',
                'created_at' => '2025-11-26 09:05:51',
                'updated_at' => '2025-11-26 09:05:51',
            ],
            [
                'id' => 5,
                'user_id' => 8,
                'asset_id' => 3,
                'floor_id' => 3,
                'description' => 'testt 4',
                'notes' => 'mesti kali ni jadi',
                'status' => 'pending',
                'created_at' => '2025-11-26 09:07:10',
                'updated_at' => '2025-11-26 09:07:10',
            ],
            [
                'id' => 6,
                'user_id' => 6,
                'asset_id' => 2,
                'floor_id' => 2,
                'description' => 'testt 5',
                'notes' => 'jadi tak',
                'status' => 'in progress',
                'created_at' => '2025-11-26 09:08:02',
                'updated_at' => '2025-11-26 09:18:05',
            ],
            [
                'id' => 7,
                'user_id' => 11,
                'asset_id' => 2,
                'floor_id' => 2,
                'description' => 'clear',
                'notes' => 'clear',
                'status' => 'pending',
                'created_at' => '2025-11-27 04:41:28',
                'updated_at' => '2025-11-27 04:41:28',
            ],
        ]);
    }
}

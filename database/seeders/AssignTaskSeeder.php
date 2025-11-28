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
                'asset_id' => 2,
                'floor_id' => 1,
                'user_id' => 2,
                'description' => 'jadi tak',
                'notes' => 'test',
                'status' => 'done',
                'created_at' => '2025-11-25 03:13:17',
                'updated_at' => '2025-11-26 09:18:24',
            ],
            [
                'asset_id' => 2,
                'floor_id' => 1,
                'user_id' => 2,
                'description' => 'test 2',
                'notes' => 'mesti knejadi',
                'status' => 'in_progress',
                'created_at' => '2025-11-26 09:05:04',
                'updated_at' => '2025-11-26 09:18:03',
            ],
            [
                'asset_id' => 3,
                'floor_id' => 2,
                'user_id' => 1,
                'description' => 'testt3',
                'notes' => 'jadi tak',
                'status' => 'pending',
                'created_at' => '2025-11-26 09:05:51',
                'updated_at' => '2025-11-26 09:05:51',
            ],
            [
                'asset_id' => 3,
                'floor_id' => 3,
                'user_id' => 1,
                'description' => 'testt 4',
                'notes' => 'mesti kali ni jadi',
                'status' => 'pending',
                'created_at' => '2025-11-26 09:07:10',
                'updated_at' => '2025-11-26 09:07:10',
            ],
            [
                'asset_id' => 2,
                'floor_id' => 2,
                'user_id' => 1,
                'description' => 'testt 5',
                'notes' => 'jadi tak',
                'status' => 'in_progress',
                'created_at' => '2025-11-26 09:08:02',
                'updated_at' => '2025-11-26 09:18:05',
            ],
            [
                'asset_id' => 2,
                'floor_id' => 2,
                'user_id' => 1,
                'description' => 'clear',
                'notes' => 'clear',
                'status' => 'pending',
                'created_at' => '2025-11-27 04:41:28',
                'updated_at' => '2025-11-27 04:41:28',
            ],
        ]);
    }
}

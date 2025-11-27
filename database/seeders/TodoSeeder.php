<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user (or use a specific user ID)
        $user = User::first();

        if (!$user) {
            $this->command->info('No users found, skipping todo seeder.');
            return;
        }

        DB::table('todos')->insert([
            [
                'todo' => 'Check all full devices today',
                'status' => 'pending',
                'userID' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'todo' => 'Review maintenance schedule',
                'status' => 'pending',
                'userID' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'todo' => 'Update asset locations on map',
                'status' => 'pending',
                'userID' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'todo' => 'Send weekly device report',
                'status' => 'done',
                'userID' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}


<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'profile_photo' => 'placeholder.jpg',
                'email_verified_at' => now(),
                'password' => '$2y$12$TTXZRHJFodaDzE6xJyQyx.vaTwU.X9MVxjFDM1/30/x.uCYn9t6vm',
                'role' => 1,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@gmail.com',
                'profile_photo' => 'placeholder.jpg',
                'email_verified_at' => now(),
                'password' => '$2y$12$TTXZRHJFodaDzE6xJyQyx.vaTwU.X9MVxjFDM1/30/x.uCYn9t6vm',
                'role' => 2,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],,
            [
                'name' => 'Supervisor',
                'email' => 'supervisor@gmail.com',
                'profile_photo' => 'placeholder.jpg',
                'email_verified_at' => now(),
                'password' => '$2y$12$TTXZRHJFodaDzE6xJyQyx.vaTwU.X9MVxjFDM1/30/x.uCYn9t6vm',
                'role' => 4,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

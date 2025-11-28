<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            KategoriSeeder::class,
        ]);
        $this->call(AssetSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(DeviceSeeder::class);
        $this->call(FloorSeeder::class);
        $this->call(AssignTaskSeeder::class);
        $this->call(SensorSeeder::class);
        $this->call(TaskSeeder::class);
        $this->call(TodoSeeder::class);
    }
}

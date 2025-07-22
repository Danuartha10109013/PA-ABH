<?php

namespace Database\Seeders;

use App\Models\Beginning;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Beginning::create([
            'amount' => 100000000, 
        ]);

        $this->call([
            UserSeeder::class,
            Category::class,
        ]);
    }
}

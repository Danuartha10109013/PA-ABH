<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'CFO Tekmt',
            'email' => 'cfo@gmail.com',
            'role' => 'cfo',
            'password' => bcrypt('CFOtappp1'),
        ]);
        User::create([
            'name' => 'CEO Tekmt',
            'email' => 'ceo@gmail.com',
            'role' => 'ceo',
            'password' => bcrypt('CEOtappp1'),
        ]);
    }
}

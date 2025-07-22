<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Category extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'PDAM',
                'type' => 1,
                'user_id' => 1,
            ],
            [
                'name' => 'PIUTANG',
                'type' => 0,
                'user_id' => 1,
            ],
        ];

        foreach ($categories as $category) {
            Payment::create($category);
        }
    }
}

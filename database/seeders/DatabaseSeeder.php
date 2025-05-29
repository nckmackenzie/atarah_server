<?php

namespace Database\Seeders;

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

        User::factory()->create([
            'name' => 'Jecinta',
            'email' => 'grace@atarahsolutions.com',
            'contact' => '0700000000',
            'password' => 'password',
            'user_type' => 'admin',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'telephone' => '770000000',
            'role' => 'admin',
            'password' => bcrypt('admin123'),
        ]);

        // Quelques utilisateurs aléatoires
        User::factory(10)->create();
    }
}

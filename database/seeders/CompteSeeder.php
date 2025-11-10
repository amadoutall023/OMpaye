<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Compte;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    public function run(): void
    {
        // Crée un compte pour chaque user existant
        User::all()->each(function ($user) {
            Compte::create([
                'user_id' => $user->id,
                'solde' => 0,
            ]);
        });
    }
}

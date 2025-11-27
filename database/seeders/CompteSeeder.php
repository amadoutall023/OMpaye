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
            $numeroCompte = $this->generateNumeroCompte();
            Compte::create([
                'user_id' => $user->id,
                'numero_compte' => $numeroCompte,
                'solde' => 0,
            ]);
        });
    }

    /**
     * Générer un numéro de compte unique
     */
    private function generateNumeroCompte(): string
    {
        do {
            $numero = 'KC-' . str_pad(random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        } while (\App\Models\Compte::where('numero_compte', $numero)->exists());

        return $numero;
    }
}

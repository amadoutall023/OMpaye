<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Compte;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Générer un numéro de compte unique
        $numeroCompte = $this->generateNumeroCompte();

        // Créer automatiquement le compte rattaché
        Compte::create([
            'user_id' => $user->id,
            'numero_compte' => $numeroCompte,
            'solde' => 0,
        ]);
    }

    /**
     * Générer un numéro de compte unique
     */
    private function generateNumeroCompte(): string
    {
        do {
            $numero = str_pad(random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        } while (Compte::where('numero_compte', $numero)->exists());

        return $numero;
    }
}

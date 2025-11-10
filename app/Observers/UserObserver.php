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
        // Créer automatiquement le compte rattaché
        Compte::create([
            'user_id' => $user->id,
            'solde' => 0,
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La colonne tokenable_id a déjà été convertie en UUID dans la migration précédente
        // Cette migration est maintenant inutile et peut être supprimée
    }

    public function down(): void
    {
        // Rien à faire car la conversion a été faite dans la migration précédente
    }
};

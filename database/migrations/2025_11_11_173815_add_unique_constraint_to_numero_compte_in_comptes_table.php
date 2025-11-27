<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // La contrainte unique a déjà été ajoutée dans la migration précédente
        // Cette migration est donc inutile et peut être supprimée
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rien à faire car la contrainte était déjà présente
    }
};

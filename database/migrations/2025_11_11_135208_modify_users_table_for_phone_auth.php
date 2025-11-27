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
        Schema::table('users', function (Blueprint $table) {
            // Rendre l'email optionnel
            $table->string('email')->nullable()->change();
            // Supprimer la contrainte unique sur email
            $table->dropUnique(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remettre l'email obligatoire et unique
            $table->string('email')->nullable(false)->unique()->change();
            // Rendre le téléphone optionnel
            $table->string('telephone')->nullable()->change();
            // Supprimer la contrainte unique sur telephone
            $table->dropUnique(['telephone']);
        });
    }
};

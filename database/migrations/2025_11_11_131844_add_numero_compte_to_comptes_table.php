<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->string('numero_compte')->nullable()->unique()->after('user_id');
        });

        // Générer des numéros de compte pour les comptes existants
        $comptes = DB::table('comptes')->get();
        foreach ($comptes as $index => $compte) {
            $numeroCompte = 'CLI' . str_pad(($index + 1), 4, '0', STR_PAD_LEFT);
            DB::table('comptes')->where('id', $compte->id)->update(['numero_compte' => $numeroCompte]);
        }

        // Maintenant rendre la colonne NOT NULL
        Schema::table('comptes', function (Blueprint $table) {
            $table->string('numero_compte')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn('numero_compte');
        });
    }
};

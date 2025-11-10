<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convertir les valeurs bigint existantes en chaînes UUID
        DB::statement("ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE varchar(36);");

        // Puis convertir en UUID
        DB::statement("ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE uuid USING tokenable_id::uuid;");
    }

    public function down(): void
    {
        // Pour le rollback, on ne peut pas convertir les UUIDs en bigint directement
        // On va juste changer le type en varchar pour éviter l'erreur
        DB::statement("ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE varchar(36);");
    }
};

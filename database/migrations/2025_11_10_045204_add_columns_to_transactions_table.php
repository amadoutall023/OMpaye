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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignUuid('compte_id')->nullable()->constrained('comptes')->cascadeOnDelete();
            $table->foreignUuid('merchant_id')->nullable()->constrained('marchands')->cascadeOnDelete();
            $table->string('reference')->unique()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['compte_id']);
            $table->dropForeign(['merchant_id']);
            $table->dropColumn(['compte_id', 'merchant_id', 'reference']);
        });
    }
};

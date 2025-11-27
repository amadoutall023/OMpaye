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
        Schema::create('otp_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable(); // nullable pour les tokens d'enregistrement
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('token', 6);
            $table->string('purpose'); // register, login, password_reset, etc.
            $table->json('data')->nullable(); // pour stocker des données temporaires (téléphone pour register)
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'purpose']);
            $table->index(['purpose', 'used', 'expires_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_tokens');
    }
};

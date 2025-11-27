<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OtpToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'purpose',
        'data',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifier si le token est expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Marquer le token comme utilisé
     */
    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    /**
     * Générer un nouveau token OTP
     */
    public static function generateToken(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Créer un token OTP pour un utilisateur
     */
    public static function createForUser(User $user, string $purpose = 'login', int $expiresInMinutes = 10): self
    {
        // Supprimer les anciens tokens non utilisés pour cet utilisateur et ce purpose
        self::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->delete();

        return self::create([
            'user_id' => $user->id,
            'token' => self::generateToken(),
            'purpose' => $purpose,
            'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
            'used' => false
        ]);
    }

    /**
     * Vérifier un token OTP
     */
    public static function verifyToken(User $user, string $token, string $purpose = 'login'): ?self
    {
        return self::where('user_id', $user->id)
            ->where('token', $token)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }
}

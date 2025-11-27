<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Compte extends Model
{
    use HasFactory,HasUuids;

    protected $fillable = [
        'user_id',
        'numero_compte',
        'solde',
    ];

    protected $casts = [
        'solde' => 'float',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

      public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'compte_id', 'id');
    }

    public function recalculerSolde()
    {
        $this->solde = $this->transactions()->where('statut', 'valide')->get()->reduce(function ($carry, $transaction) {
            switch ($transaction->type) {
                case 'depot':
                    return $carry + $transaction->montant;
                case 'retrait':
                case 'paiement':
                    return $carry - $transaction->montant;
                case 'transfert':
                    // Pour les transferts, vérifier si c'est émetteur ou destinataire
                    // Si le compte est l'émetteur, soustraire; sinon ajouter
                    // Mais pour simplifier, supposons que les transferts sont sortants pour ce compte
                    return $carry - $transaction->montant;
                default:
                    return $carry;
            }
        }, 0);

        $this->save();
        \Illuminate\Support\Facades\Log::info('Solde recalculé', ['compte_id' => $this->id, 'nouveau_solde' => $this->solde]);
    }

}

<?php

// app/Models/Transaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'compte_id', 'merchant_id', 'type', 'montant', 'statut', 'description', 'reference'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->getKey()) {
                $transaction->{$transaction->getKeyName()} = Str::uuid()->toString();
            }
            if (!$transaction->reference) {
                $transaction->reference = 'TXN-' . strtoupper(Str::random(10));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero' => 'required|string',
            'montant' => 'required|numeric|min:0.01',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $numero = $this->input('numero');

            // Vérifier si c'est un numéro de téléphone (9 chiffres commençant par 7)
            if (preg_match('/^7\d{8}$/', $numero)) {
                // C'est un numéro de téléphone
                $this->merge(['type_destination' => 'telephone']);

                // Vérifier que l'utilisateur existe
                $user = \App\Models\User::where('telephone', $numero)->first();
                if (!$user) {
                    $validator->errors()->add('numero', 'Destinataire introuvable.');
                } elseif (!$user->compte) {
                    $validator->errors()->add('numero', 'Le destinataire n\'a pas de compte.');
                } else {
                    $this->merge(['destinataire' => $user]);
                }
            } else {
                // Considérer comme code marchand
                $this->merge(['type_destination' => 'marchand']);

                // Vérifier que le marchand existe
                $marchand = \App\Models\Marchand::where('code', $numero)->first();
                if (!$marchand) {
                    $validator->errors()->add('numero', 'Marchand introuvable.');
                } else {
                    $this->merge(['marchand' => $marchand]);
                }
            }
        });
    }

    public function isTelephone(): bool
    {
        return $this->input('type_destination') === 'telephone';
    }

    public function isMarchand(): bool
    {
        return $this->input('type_destination') === 'marchand';
    }

    public function getDestinataire()
    {
        return $this->input('destinataire');
    }

    public function getMarchand()
    {
        return $this->input('marchand');
    }

    public function getMontant(): float
    {
        return (float) $this->input('montant');
    }
}
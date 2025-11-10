<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Transaction::where('user_id', Auth::id())->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:depot,paiement,transfert',
            'montant' => 'required|numeric|min:1',
            'description' => 'nullable|string'
        ]);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction enregistrée.',
            'data' => $transaction
        ], 201);
    }

    public function depot(Request $request)
    {
        $request->validate([
            'montant' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $compte = $user->compte;

        // Créditer le compte
        $compte->solde += $request->montant;
        $compte->save();

        // Enregistrer la transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'compte_id' => $compte->id,
            'type' => 'depot',
            'montant' => $request->montant,
            'statut' => 'valide',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Dépôt effectué avec succès.',
            'data' => [
                'transaction' => $transaction,
                'solde_actuel' => $compte->fresh()->solde
            ]
        ], 201);
    }

    public function retrait(Request $request)
    {
        $request->validate([
            'montant' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $compte = $user->compte;

        if ($compte->solde < $request->montant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Solde insuffisant.'
            ], 400);
        }

        DB::transaction(function() use ($compte, $user, $request) {
            $compte->solde -= $request->montant;
            $compte->save();

            Transaction::create([
                'user_id' => $user->id,
                'compte_id' => $compte->id,
                'type' => 'retrait',
                'montant' => $request->montant,
                'statut' => 'valide',
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Retrait effectué avec succès.',
            'data' => [
                'user_id' => $user->id,
                'type' => 'retrait',
                'montant' => $request->montant,
                'solde_actuel' => $compte->fresh()->solde
            ]
        ]);
    }

    public function paiementMarchand(Request $request)
    {
        $request->validate([
            'code_marchand' => 'required|string|exists:marchands,code',
            'montant' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $compte = $user->compte;

        if ($compte->solde < $request->montant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Solde insuffisant.'
            ], 400);
        }

        $marchand = \App\Models\Marchand::where('code', $request->code_marchand)->first();
        if (!$marchand) {
            return response()->json([
                'status' => 'error',
                'message' => 'Marchand introuvable.'
            ], 404);
        }

        DB::transaction(function() use ($compte, $marchand, $request, $user) {
            // Déduire de l'utilisateur
            $compte->solde -= $request->montant;
            $compte->save();

            // Créer transaction pour l'utilisateur
            Transaction::create([
                'user_id' => $user->id,
                'compte_id' => $compte->id,
                'merchant_id' => $marchand->id,
                'type' => 'paiement',
                'montant' => $request->montant,
                'statut' => 'valide',
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Paiement effectué avec succès.',
            'data' => [
                'user_id' => $user->id,
                'merchant_id' => $marchand->id,
                'type' => 'paiement',
                'montant' => $request->montant,
                'solde_actuel' => $compte->fresh()->solde
            ]
        ]);
    }

    public function transfert(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|exists:users,telephone',
            'montant' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $compte = $user->compte;

        if ($compte->solde < $request->montant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Solde insuffisant.'
            ], 400);
        }

        $destinataire = \App\Models\User::where('telephone', $request->numero)->first();

        DB::transaction(function() use ($compte, $destinataire, $request, $user) {
            // Déduire de l'utilisateur
            $compte->solde -= $request->montant;
            $compte->save();

            // Ajouter au destinataire
            $destCompte = $destinataire->compte;
            $destCompte->solde += $request->montant;
            $destCompte->save();

            // Créer transaction pour l'émetteur
            Transaction::create([
                'user_id' => $user->id,
                'compte_id' => $compte->id,
                'type' => 'transfert',
                'montant' => $request->montant,
                'statut' => 'valide',
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Transfert effectué avec succès.',
            'data' => [
                'emetteur_id' => $user->id,
                'destinataire_id' => $destinataire->id,
                'type' => 'transfert',
                'montant' => $request->montant,
                'solde_actuel' => $compte->fresh()->solde
            ]
        ]);
    }
}

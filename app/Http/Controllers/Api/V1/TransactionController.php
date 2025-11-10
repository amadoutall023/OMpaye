<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
            'type' => 'depot',
            'montant' => $request->montant,
            'statut' => 'valide',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Dépôt effectué avec succès.',
            'data' => $transaction
        ], 201);
    }
}

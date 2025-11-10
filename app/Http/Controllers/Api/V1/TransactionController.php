<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     title="Transaction",
 *     description="Modèle représentant une transaction",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="compte_id", type="integer", example=1),
 *     @OA\Property(property="merchant_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="type", type="string", enum={"depot", "retrait", "paiement", "transfert"}, example="depot"),
 *     @OA\Property(property="montant", type="number", format="float", example=100.50),
 *     @OA\Property(property="statut", type="string", enum={"en_attente", "valide", "annule"}, example="valide"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TransactionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/transactions",
     *     summary="Lister les transactions de l'utilisateur",
     *     description="Récupérer toutes les transactions de l'utilisateur connecté",
     *     operationId="getTransactions",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des transactions récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Transaction"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/v1/transactions/depot",
     *     summary="Effectuer un dépôt",
     *     description="Créditer le compte de l'utilisateur avec un montant spécifié",
     *     operationId="depot",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"montant"},
     *             @OA\Property(property="montant", type="number", format="float", minimum=0.01, example=100.50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dépôt effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Dépôt effectué avec succès."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction", ref="#/components/schemas/Transaction"),
     *                 @OA\Property(property="solde_actuel", type="number", format="float", example=1100.50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The montant field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/v1/transactions/retrait",
     *     summary="Effectuer un retrait",
     *     description="Débiter le compte de l'utilisateur avec un montant spécifié",
     *     operationId="retrait",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"montant"},
     *             @OA\Property(property="montant", type="number", format="float", minimum=0.01, example=50.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Retrait effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Retrait effectué avec succès."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="type", type="string", example="retrait"),
     *                 @OA\Property(property="montant", type="number", format="float", example=50.00),
     *                 @OA\Property(property="solde_actuel", type="number", format="float", example=950.50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solde insuffisant",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Solde insuffisant.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The montant field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/v1/transactions/paiement",
     *     summary="Effectuer un paiement marchand",
     *     description="Payer un marchand en utilisant le code marchand",
     *     operationId="paiementMarchand",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code_marchand","montant"},
     *             @OA\Property(property="code_marchand", type="string", example="OMN001"),
     *             @OA\Property(property="montant", type="number", format="float", minimum=0.01, example=25.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiement effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Paiement effectué avec succès."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="merchant_id", type="integer", example=1),
     *                 @OA\Property(property="type", type="string", example="paiement"),
     *                 @OA\Property(property="montant", type="number", format="float", example=25.00),
     *                 @OA\Property(property="solde_actuel", type="number", format="float", example=975.50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solde insuffisant",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Solde insuffisant.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Marchand introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Marchand introuvable.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The code_marchand field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/v1/transactions/transfert",
     *     summary="Effectuer un transfert",
     *     description="Transférer de l'argent vers un autre utilisateur via son numéro de téléphone",
     *     operationId="transfert",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numero","montant"},
     *             @OA\Property(property="numero", type="string", example="520-518-6511"),
     *             @OA\Property(property="montant", type="number", format="float", minimum=0.01, example=100.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfert effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Transfert effectué avec succès."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="emetteur_id", type="integer", example=1),
     *                 @OA\Property(property="destinataire_id", type="integer", example=2),
     *                 @OA\Property(property="type", type="string", example="transfert"),
     *                 @OA\Property(property="montant", type="number", format="float", example=100.00),
     *                 @OA\Property(property="solde_actuel", type="number", format="float", example=900.50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solde insuffisant",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Solde insuffisant.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Destinataire introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Destinataire introuvable.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The numero field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * @OA\Schema(
 *     schema="AccountResponse",
 *     type="object",
 *     title="Réponse compte",
 *     description="Informations détaillées d'un compte",
 *     @OA\Property(property="numerocompte", type="string", example="KC-00001234"),
 *     @OA\Property(property="telephone", type="string", example="+22177xxxxxxx"),
 *     @OA\Property(property="solde", type="number", format="float", example=12500),
 *     @OA\Property(property="qrCode", type="string", description="QR Code en base64", example="data:image/png;base64,..."),
 *     @OA\Property(property="transactions_summary", type="object",
 *         @OA\Property(property="count", type="integer", example=12),
 *         @OA\Property(property="last", type="string", format="date-time", example="2025-11-10T14:22:00Z")
 *     )
 * )
 */
class AccountController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/accounts/{numerocompte}",
     *     summary="Informations du compte",
     *     description="Récupérer les informations détaillées d'un compte par numéro",
     *     operationId="getAccount",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="numerocompte",
     *         in="path",
     *         required=true,
     *         description="Numéro du compte",
     *         @OA\Schema(type="string", example="KC-00001234")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations du compte récupérées avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/AccountResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
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
    public function show($numerocompte)
    {
        try {
            $compte = Compte::where('numero_compte', $numerocompte)->with('user')->first();

            if (!$compte) {
                return response()->json([
                    'message' => 'Compte non trouvé'
                ], 404);
            }

            if (!$compte->user) {
                return response()->json([
                    'message' => 'Utilisateur associé au compte non trouvé'
                ], 404);
            }

            // Générer le QR Code
            $qrCodeData = json_encode([
                'numerocompte' => $compte->numero_compte,
                'telephone' => $compte->user->telephone,
                'timestamp' => now()->toISOString()
            ]);

            $qrCodeFormat = 'png';
            $mimeType = 'image/png';
            try {
                $qrCode = base64_encode(QrCode::format('png')->size(200)->generate($qrCodeData));
            } catch (\Throwable $e) {
                // Fallback si PNG n'est pas disponible, utiliser SVG
                try {
                    $qrCode = base64_encode(QrCode::format('svg')->size(200)->generate($qrCodeData));
                    $qrCodeFormat = 'svg';
                    $mimeType = 'image/svg+xml';
                } catch (\Throwable $e2) {
                    // Si SVG échoue aussi, utiliser une chaîne vide ou un placeholder
                    $qrCode = '';
                    $mimeType = 'text/plain';
                }
            }

            // Récupérer le résumé des transactions
            $transactionsSummary = Transaction::where('compte_id', $compte->id)
                ->selectRaw('COUNT(*) as count, MAX(created_at) as last')
                ->first();

            return response()->json([
                'numerocompte' => $compte->numero_compte,
                'telephone' => $compte->user->telephone,
                'solde' => $compte->solde,
                'qrCode' => 'data:' . $mimeType . ';base64,' . $qrCode,
                'transactions_summary' => [
                    'count' => $transactionsSummary->count ?? 0,
                    'last' => $transactionsSummary->last?->toISOString()
                ]
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error in show', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accounts/{numerocompte}/balance",
     *     summary="Solde du compte",
     *     description="Récupérer uniquement le solde d'un compte",
     *     operationId="getAccountBalance",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="numerocompte",
     *         in="path",
     *         required=true,
     *         description="Numéro du compte",
     *         @OA\Schema(type="string", example="KC-00001234")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solde récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="numerocompte", type="string", example="KC-00001234"),
     *             @OA\Property(property="solde", type="number", format="float", example=12500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     )
     * )
     */
    public function balance($numerocompte)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Balance method called', ['numerocompte' => $numerocompte]);

            // Valider le format du numéro de compte
            if (!preg_match('/^[A-Z0-9\-]+$/', $numerocompte)) {
                return response()->json([
                    'message' => 'Format de numéro de compte invalide'
                ], 400);
            }

            $compte = Compte::where('numero_compte', $numerocompte)->first();

            if (!$compte) {
                return response()->json([
                    'message' => 'Compte non trouvé'
                ], 404);
            }

            return response()->json([
                'numerocompte' => $compte->numero_compte,
                'solde' => $compte->solde
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error in balance', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/accounts/{numerocompte}/transactions",
     *     summary="Transactions du compte",
     *     description="Récupérer la liste paginée des transactions d'un compte",
     *     operationId="getAccountTransactions",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="numerocompte",
     *         in="path",
     *         required=true,
     *         description="Numéro du compte",
     *         @OA\Schema(type="string", example="KC-00001234")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Nombre d'éléments par page",
     *         @OA\Schema(type="integer", default=20, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Numéro de la page",
     *         @OA\Schema(type="integer", default=1, minimum=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transactions récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Transaction")),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="total", type="integer", example=45),
     *             @OA\Property(property="last_page", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     )
     * )
     */
    public function transactions(Request $request, $numerocompte)
    {
        $compte = Compte::where('numero_compte', $numerocompte)->first();

        if (!$compte) {
            return response()->json([
                'message' => 'Compte non trouvé'
            ], 404);
        }

        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $transactions = Transaction::where('compte_id', $compte->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json($transactions);
    }
}

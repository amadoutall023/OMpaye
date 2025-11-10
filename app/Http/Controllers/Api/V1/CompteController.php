<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CompteRequest;
use App\Models\Compte;
use App\Models\User;
use App\Http\Resources\CompteResource;

/**
 * @OA\Schema(
 *     schema="Compte",
 *     type="object",
 *     title="Compte",
 *     description="Modèle représentant un compte utilisateur",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="numero_compte", type="string", example="1234567890"),
 *     @OA\Property(property="solde", type="number", format="float", example=1000.50),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CompteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/compte",
     *     summary="Afficher le compte de l'utilisateur connecté",
     *     description="Récupérer les informations du compte de l'utilisateur authentifié",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations du compte récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Compte")
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
    public function show()
    {
        $compte = auth()->user()->compte;

        return response()->json([
            'success' => true,
            'data' => new CompteResource($compte),
        ]);
    }
}
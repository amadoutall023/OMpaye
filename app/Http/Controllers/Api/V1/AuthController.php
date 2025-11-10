<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * @OA\Info(
 *     title="API OMPAYE",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes et transactions OMPAYE"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Serveur API principal"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="Utilisateur",
 *     description="Modèle représentant un utilisateur",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="telephone", type="string", example="+221771234567"),
 *     @OA\Property(property="role", type="string", enum={"client", "marchand", "admin"}, example="client"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Connexion utilisateur",
     *     description="Authentifier un utilisateur et retourner un token d'accès",
     *     operationId="login",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="admin123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Connexion réussie."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="access_token", type="string", example="1|abc123...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Identifiants invalides.")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Identifiants invalides.'
            ], 401);
        }

        $user = Auth::user();

        // Création d'un token Passport
        $token = $user->createToken('access-token', ['*']);

        return response()->json([
            'status' => 'success',
            'message' => 'Connexion réussie.',
            'data' => [
                'user' => $user,
                'access_token' => $token->plainTextToken // retourne le token en clair
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     summary="Déconnexion utilisateur",
     *     description="Révoquer tous les tokens d'accès de l'utilisateur",
     *     operationId="logout",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/refresh",
     *     summary="Rafraîchir le token d'accès",
     *     description="Révoquer les anciens tokens et créer un nouveau token d'accès",
     *     operationId="refresh",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Token rafraîchi."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="1|def456...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        $user = $request->user();

        // Supprimer les anciens tokens
        $user->tokens()->delete();

        // Créer un nouveau token
        $token = $user->createToken('access-token', ['*']);

        return response()->json([
            'status' => 'success',
            'message' => 'Token rafraîchi.',
            'data' => [
                'access_token' => $token->plainTextToken
            ]
        ]);
    }
}

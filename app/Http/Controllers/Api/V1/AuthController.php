<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\TwilioService;
use App\Models\OtpToken;

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
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }
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
    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Demander un code OTP pour la connexion",
     *     description="Vérifier les identifiants et envoyer un code OTP par email",
     *     operationId="requestOtp",
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
     *         description="Code OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Code OTP envoyé à votre adresse email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Identifiants invalides.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The email field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Identifiants invalides.'
            ], 401);
        }

        $user = Auth::user();

        // Générer et envoyer le code OTP par email
        $otpToken = OtpToken::createForUser($user, 'login');

        // Envoyer l'email avec le code OTP
        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\OtpCodeMail($user, $otpToken));

        return response()->json([
            'status' => 'success',
            'message' => 'Code OTP envoyé à votre adresse email.',
            'data' => [
                'email' => $user->email,
                'expires_in' => 600, // 10 minutes en secondes
                'otp_code' => $otpToken->token // Pour test seulement
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
    /**
     * @OA\Post(
     *     path="/api/v1/verify-otp",
     *     summary="Vérifier le code OTP et se connecter",
     *     description="Valider le code OTP et retourner un token d'accès",
     *     operationId="verifyOtp",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"otp_code"},
     *             @OA\Property(property="otp_code", type="string", example="316969")
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
     *         response=400,
     *         description="Code OTP invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Code OTP invalide ou expiré.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The otp_code field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6'
        ]);

        // Récupérer tous les tokens OTP non utilisés pour 'login'
        $otpTokens = \App\Models\OtpToken::where('purpose', 'login')
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->get();

        $validToken = null;
        $user = null;

        foreach ($otpTokens as $token) {
            if ($request->otp_code === $token->token) {
                $validToken = $token;
                $user = $token->user;
                break;
            }
        }

        if (!$validToken || !$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code OTP invalide ou expiré.'
            ], 400);
        }

        // Marquer le token comme utilisé
        $validToken->update(['is_used' => true]);

        // Créer un token d'accès
        $token = $user->createToken('access-token', ['*']);

        return response()->json([
            'status' => 'success',
            'message' => 'Connexion réussie.',
            'data' => [
                'user' => $user,
                'access_token' => $token->plainTextToken
            ]
        ]);
    }

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

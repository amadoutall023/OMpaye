<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\OtpToken;
use App\Mail\OtpCodeMail;

/**
 * @OA\Info(
 *     title="API OMPAYE",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes et transactions OMPAYE"
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Serveur de développement local"
 * )
 * @OA\Server(
 *     url="https://ompaye-api.onrender.com",
 *     description="Serveur de production"
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
     *     path="/api/v1/auth/register",
     *     summary="S'inscrire avec numéro de téléphone",
     *     description="Demander un code OTP par SMS pour créer un nouveau compte",
     *     operationId="register",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telephone"},
     *             @OA\Property(property="telephone", type="string", example="+221771234567")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP envoyé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="otp_sent"),
     *             @OA\Property(property="message", type="string", example="OTP envoyé au +221771234567"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="expires_in", type="integer", example=600)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Numéro de téléphone déjà utilisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Ce numéro de téléphone est déjà utilisé.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The telephone field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Se connecter avec numéro de téléphone et mot de passe",
     *     description="Vérifier les identifiants et envoyer un code OTP par SMS pour la connexion",
     *     operationId="login",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telephone","password"},
     *             @OA\Property(property="telephone", type="string", example="+221771234567"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Code OTP envoyé à votre numéro de téléphone."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="expires_in", type="integer", example=600)
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
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The telephone field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     summary="S'inscrire avec numéro de téléphone",
     *     description="Demander un code OTP par SMS pour créer un nouveau compte",
     *     operationId="register",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telephone"},
     *             @OA\Property(property="telephone", type="string", example="+221771234567")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP envoyé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="otp_sent"),
     *             @OA\Property(property="message", type="string", example="OTP envoyé au +221771234567"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="expires_in", type="integer", example=600)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Numéro de téléphone déjà utilisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Ce numéro de téléphone est déjà utilisé.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The telephone field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string|regex:/^[7-8]\d{8}$/|unique:users,telephone'
        ]);

        // Normaliser le numéro de téléphone
        $telephone = $request->telephone;
        if (preg_match('/^[7-8]\d{8}$/', $telephone)) {
            $telephone = '+221' . $telephone;
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = User::where('telephone', $telephone)->first();
        if ($existingUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ce numéro de téléphone est déjà utilisé.'
            ], 400);
        }

        // Créer un token OTP temporaire pour l'enregistrement (sans user_id car l'utilisateur n'existe pas encore)
        $otpToken = OtpToken::create([
            'token' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'purpose' => 'register',
            'expires_at' => now()->addMinutes(10),
            'data' => json_encode(['telephone' => $telephone]),
            'used' => false
        ]);

        // Envoyer l'email avec le code OTP
        try {
            Mail::to($telephone . '@ompaye.local')->send(new OtpCodeMail($otpToken->token));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.'
            ], 500);
        }

        return response()->json([
            'status' => 'otp_sent',
            'message' => "OTP envoyé au {$telephone}",
            'data' => [
                'telephone' => $telephone,
                'expires_in' => 600 // 10 minutes
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
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
     *     path="/api/v1/auth/verify",
     *     summary="Vérifier le code OTP et créer le compte",
     *     description="Valider le code OTP envoyé par SMS et créer le compte utilisateur avec mot de passe",
     *     operationId="verifyOtp",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telephone","otp","password"},
     *             @OA\Property(property="telephone", type="string", example="+221771234567"),
     *             @OA\Property(property="otp", type="string", example="123456"),
     *             @OA\Property(property="password", type="string", format="password", example="MonMotDePasseSecurise!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="created"),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="access_token", type="string", example="1|abc123..."),
     *                 @OA\Property(property="expires_in", type="integer", example=3600),
     *                 @OA\Property(property="account", type="object",
     *                     @OA\Property(property="numerocompte", type="string", example="KC-00001234"),
     *                     @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                     @OA\Property(property="solde", type="number", format="float", example=0)
     *                 )
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
     *             @OA\Property(property="message", type="string", example="The otp field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8'
        ]);

        // Normaliser le numéro de téléphone
        $telephone = $request->telephone;
        if (preg_match('/^[7-8]\d{8}$/', $telephone)) {
            $telephone = '+221' . $telephone;
        }

        // Récupérer le token OTP pour l'enregistrement
        $otpToken = OtpToken::where('purpose', 'register')
            ->where('token', $request->otp)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code OTP invalide ou expiré.'
            ], 400);
        }

        // Vérifier que le téléphone correspond
        $data = json_decode($otpToken->data, true);
        if ($data['telephone'] !== $telephone) {
            return response()->json([
                'status' => 'error',
                'message' => 'Téléphone ne correspond pas au code OTP.'
            ], 400);
        }

        // Créer l'utilisateur
        $user = User::create([
            'name' => 'Utilisateur ' . substr($telephone, -4), // Nom temporaire
            'telephone' => $telephone,
            'password' => bcrypt($request->password),
            'role' => 'client'
        ]);

        // Générer le numéro de compte
        $numeroCompte = $this->generateNumeroCompte();

        // Créer le compte
        $compte = \App\Models\Compte::create([
            'user_id' => $user->id,
            'numero_compte' => $numeroCompte,
            'solde' => 0
        ]);

        // Marquer le token comme utilisé
        $otpToken->update(['used' => true]);

        // Créer un token d'accès pour l'utilisateur nouvellement créé
        $token = $user->createToken('access-token', []);

        return response()->json([
            'status' => 'created',
            'message' => 'Compte créé avec succès.',
            'data' => [
                'access_token' => $token->accessToken,
                'expires_in' => 3600,
                'account' => [
                    'numerocompte' => $compte->numero_compte,
                    'telephone' => $telephone,
                    'solde' => $compte->solde
                ]
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Se connecter avec numéro de téléphone et mot de passe",
     *     description="Vérifier les identifiants et envoyer un code OTP par SMS pour la connexion",
     *     operationId="login",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telephone","password"},
     *             @OA\Property(property="telephone", type="string", example="+221771234567"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Code OTP envoyé à votre numéro de téléphone."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="expires_in", type="integer", example=600)
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
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The telephone field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'telephone' => 'required|string',
                'password' => 'required|string'
            ]);

            // Normaliser le numéro de téléphone
            $telephone = $request->telephone;
            if (preg_match('/^[7-8]\d{8}$/', $telephone)) {
                $telephone = '+221' . $telephone;
            }

            $credentials = ['telephone' => $telephone, 'password' => $request->password];

            if (!Auth::guard('web')->attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Identifiants invalides.'
                ], 401);
            }

            $user = Auth::guard('web')->user();

            if (!$user->compte) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun compte associé trouvé.'
                ], 404);
            }

            // Créer un token d'accès directement
            $token = $user->createToken('access-token', []);

            return response()->json([
                'status' => 'success',
                'message' => 'Connexion réussie.',
                'data' => [
                    'access_token' => $token->accessToken,
                    'expires_in' => 3600,
                    'account' => [
                        'numerocompte' => $user->compte->numero_compte,
                        'telephone' => $telephone
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Login error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur interne du serveur.'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/verify-login",
     *     summary="Vérifier le code OTP et se connecter",
     *     description="Valider le code OTP de connexion et retourner un token d'accès",
     *     operationId="verifyLoginOtp",
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
     *                 @OA\Property(property="access_token", type="string", example="1|abc123..."),
     *                 @OA\Property(property="expires_in", type="integer", example=3600),
     *                 @OA\Property(property="account", type="object",
     *                     @OA\Property(property="numerocompte", type="string", example="KC-00001234"),
     *                     @OA\Property(property="telephone", type="string", example="+221771234567")
     *                 )
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
     *     )
     * )
     */
    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6'
        ]);

        // Récupérer tous les tokens OTP non utilisés pour 'login'
        $otpTokens = OtpToken::where('purpose', 'login')
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
        $validToken->update(['used' => true]);

        // Créer un token d'accès
        $token = $user->createToken('access-token', []);

        return response()->json([
            'status' => 'success',
            'message' => 'Connexion réussie.',
            'data' => [
                'access_token' => $token->accessToken,
                'expires_in' => 3600,
                'account' => [
                    'numerocompte' => $user->compte->numero_compte,
                    'telephone' => $user->telephone
                ]
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
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
     * Générer un numéro de compte unique
     */
    private function generateNumeroCompte(): string
    {
        do {
            $numero = 'KC-' . str_pad(random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        } while (\App\Models\Compte::where('numero_compte', $numero)->exists());

        return $numero;
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
     *     path="/api/v1/auth/refresh",
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
        $token = $user->createToken('access-token', []);

        return response()->json([
            'status' => 'success',
            'message' => 'Token rafraîchi.',
            'data' => [
                'access_token' => $token->accessToken
            ]
        ]);
    }
}

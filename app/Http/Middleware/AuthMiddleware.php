<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Vérifier si le token Bearer est présent
            $token = $request->bearerToken();

            if (!$token) {
                return response('Token manquant.', 401);
            }

            // Vérifier le token avec Passport
            try {
                $accessToken = \Laravel\Passport\Token::findToken($token);
            } catch (\Throwable $e) {
                return response('Token invalide.', 401);
            }

            if (!$accessToken || $accessToken->revoked) {
                return response('Token invalide.', 401);
            }

            // Vérifier si le token n'est pas expiré
            if ($accessToken->expires_at && $accessToken->expires_at < now()) {
                return response('Token expiré.', 401);
            }

            // Récupérer l'utilisateur
            $user = $accessToken->user;
            if (!$user) {
                return response('Utilisateur non trouvé.', 401);
            }

            // Définir l'utilisateur authentifié
            auth()->setUser($user);

            return $next($request);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AuthMiddleware error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide.'
            ], 401);
        }
    }
}

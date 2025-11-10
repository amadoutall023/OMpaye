<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si le token Bearer est présent
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token manquant.'
            ], 401);
        }

        // Parser le token Passport (format: id|token)
        $tokenParts = explode('|', $token);
        if (count($tokenParts) !== 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format de token invalide.'
            ], 401);
        }

        $tokenId = $tokenParts[0];
        $tokenValue = $tokenParts[1];

        // Chercher le token dans la base de données (les tokens sont hashés en SHA256)
        $accessToken = DB::table('personal_access_tokens')
            ->where('id', $tokenId)
            ->where('token', hash('sha256', $tokenValue))
            ->first();

        if (!$accessToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide.'
            ], 401);
        }

        // Vérifier si le token n'est pas expiré (si expires_at est défini)
        if ($accessToken->expires_at && $accessToken->expires_at < now()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expiré.'
            ], 401);
        }

        // Récupérer l'utilisateur
        $user = \App\Models\User::find($accessToken->tokenable_id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.'
            ], 401);
        }

        // Définir l'utilisateur authentifié
        auth()->setUser($user);

        return $next($request);
    }
}

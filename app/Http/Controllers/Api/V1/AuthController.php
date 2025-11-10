<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
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

        // Création d’un token Passport
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

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie.'
        ]);
    }

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

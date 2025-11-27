<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        try {
            if (!Auth::guard($guard)->check()) {
                return response('Utilisateur non authentifié.', 401);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Auth error', ['error' => $e->getMessage()]);
            return response('Token invalide.', 401);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoggingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        Log::info('Opération API', [
            'user_id' => auth()->user()?->id,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'body' => $request->all(),
            'status' => $response->status(),
        ]);

        return $response;
    }
}

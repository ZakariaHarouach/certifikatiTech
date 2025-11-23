<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');
        
        $validApiKey = env('API_KEY', 'certifikati-api-key-2024-secure-token');
        
        if (!$apiKey || $apiKey !== $validApiKey) {
            return response()->json([
                'message' => 'Invalid or missing API key.',
            ], 401);
        }
        
        return $next($request);
    }
}


<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,...$roles): Response
    {
        $user = $request->user(); // Récupère l'utilisateur authentifié via le token

        if (!$user || !in_array($user->profilU, $roles)) {
            Log::warning('Unauthorized access attempt by user ID: ' . ($user->idU ?? 'unknown') . ' with role: ' . ($user->profilU ?? 'unknown'));
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        return $next($request);
    }
}

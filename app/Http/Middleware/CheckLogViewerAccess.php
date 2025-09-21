<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLogViewerAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('logs.login')->withErrors(['Vous devez être connecté.']);
        }

        if ($user->profilU !== 'ruetartsinmimda') {
            Auth::logout();
            return redirect()->route('logs.login')->withErrors(['Accès refusé.']);
        }

        return $next($request);
    }
}


<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->is('partenaire*', 'livreur*', 'ops*')) {
                return redirect()->route('staff.login')->with('warning', 'Veuillez vous connecter pour accéder à votre espace.');
            }
            return redirect()->route('login')->with('warning', 'Veuillez vous connecter pour continuer.');
        }

        $user = Auth::user();

        if (empty($roles) || $user->hasRole(...$roles)) {
            return $next($request);
        }

        // Redirection selon le rôle réel de l'utilisateur s'il n'a pas accès à cette zone
        if ($user->isPartner()) {
            return redirect()->route('partner.dashboard')->with('error', 'Accès non autorisé pour votre rôle.');
        }

        if ($user->isCourier()) {
            return redirect()->route('courier.dashboard')->with('error', 'Accès non autorisé pour votre rôle.');
        }

        if ($user->isOps()) {
            return redirect()->route('ops.dashboard')->with('error', 'Accès non autorisé pour votre rôle.');
        }

        return redirect()->route('feed')->with('error', 'Accès réservé.');
    }
}


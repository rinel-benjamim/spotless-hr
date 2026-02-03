<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppIsSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        // Se não há usuários admin, redirecionar para setup
        if (!User::where('role', 'admin')->exists() && !$request->is('setup*')) {
            return redirect('/setup');
        }

        // Se já há admin e está tentando acessar setup, redirecionar para dashboard
        if (User::where('role', 'admin')->exists() && $request->is('setup*')) {
            return redirect('/dashboard');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Justification;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SharePendingJustifications
{
    public function handle(Request $request, Closure $next): Response
    {
        $pendingCount = 0;

        if (auth()->check() && auth()->user()->isAdmin()) {
            $pendingCount = Justification::where('status', 'pending')->count();
        }

        Inertia::share('pendingJustificationsCount', $pendingCount);

        return $next($request);
    }
}

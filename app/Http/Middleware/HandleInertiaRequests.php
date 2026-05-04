<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    // Template raiz carregado na primeira visita
    protected $rootView = 'app';

    // Versão dos assets para cache busting
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    // Dados compartilhados com todas as páginas Inertia
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}

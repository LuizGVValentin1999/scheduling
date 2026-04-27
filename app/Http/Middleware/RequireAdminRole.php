<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia acesso às rotas administrativas para usuários sem role=admin no tenant atual.
 */
class RequireAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(
            ! auth()->user()?->isAdminOfCurrentTenant(),
            403,
            'Acesso restrito a administradores do workspace.'
        );

        return $next($request);
    }
}

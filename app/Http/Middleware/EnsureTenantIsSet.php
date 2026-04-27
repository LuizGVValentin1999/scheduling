<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que:
 *  1. Em rotas protegidas: garante que o usuário tem um tenant ativo.
 *  2. Em rotas públicas: é ignorado (não está no grupo web).
 *
 * O tenant ID resolvido aqui é consumido pelo TenantScope
 * via auth()->user()->current_tenant_id.
 */
class EnsureTenantIsSet
{
    public function handle(Request $request, Closure $next): Response
    {
        // Ignora rotas de autenticação e públicas
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if (! $user->current_tenant_id) {
            // Tenta definir o primeiro tenant disponível automaticamente
            $firstTenant = $user->tenants()->first();

            if ($firstTenant) {
                $user->update(['current_tenant_id' => $firstTenant->id]);
            } else {
                // Usuário sem nenhum tenant — redireciona para criar o primeiro
                if (! $request->routeIs('tenants.create', 'tenants.store')) {
                    return redirect()->route('tenants.create')
                                     ->with('info', 'Crie seu primeiro workspace para continuar.');
                }
            }
        }

        return $next($request);
    }
}

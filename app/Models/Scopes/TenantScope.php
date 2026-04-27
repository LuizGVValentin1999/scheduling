<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope aplicado automaticamente em todos os models tenant-scoped.
 * Filtra por tenant_id usando o tenant resolvido pelo middleware.
 * Ao criar um registro, injeta o tenant_id automaticamente via creating().
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($tenantId = self::resolveTenantId()) {
            $builder->where($model->getTable().'.tenant_id', $tenantId);
        }
    }

    public static function resolveTenantId(): ?int
    {
        // Resolve o tenant a partir do usuário autenticado na sessão atual
        return auth()->user()?->current_tenant_id;
    }
}

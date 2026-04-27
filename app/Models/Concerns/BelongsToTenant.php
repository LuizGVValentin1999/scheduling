<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait reutilizável: qualquer model que use este trait recebe
 * automaticamente o global scope de tenant e a relação tenant().
 * Uso: use BelongsToTenant; dentro do model.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        // Injeta tenant_id automaticamente ao criar registros
        static::creating(function (self $model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = TenantScope::resolveTenantId();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

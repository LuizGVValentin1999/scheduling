<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'avatar',
        'provider', 'provider_id',
        'current_tenant_id',
    ];

    protected $hidden = ['remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // --------------------------------------------------------
    // Relações
    // --------------------------------------------------------

    public function currentTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function sharedSchedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'schedule_shares', 'shared_with_user_id', 'schedule_id')
                    ->withPivot('permission')
                    ->withTimestamps();
    }

    public function calendarIntegrations(): HasMany
    {
        return $this->hasMany(CalendarIntegration::class);
    }

    // --------------------------------------------------------
    // Helpers
    // --------------------------------------------------------

    public function isAdminOf(Tenant $tenant): bool
    {
        return $this->tenants()
                    ->wherePivot('tenant_id', $tenant->id)
                    ->wherePivot('role', 'admin')
                    ->exists();
    }

    public function isAdminOfCurrentTenant(): bool
    {
        return $this->tenants()
                    ->wherePivot('tenant_id', $this->current_tenant_id)
                    ->wherePivot('role', 'admin')
                    ->exists();
    }

    public function roleInCurrentTenant(): ?string
    {
        return $this->tenants()
                    ->wherePivot('tenant_id', $this->current_tenant_id)
                    ->first()
                    ?->pivot
                    ?->role;
    }
}

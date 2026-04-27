<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'user_id', 'name', 'description',
        'working_hours', 'slot_duration', 'is_public',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'is_public'     => 'boolean',
    ];

    // --------------------------------------------------------
    // Relações
    // --------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ScheduleShare::class);
    }

    public function sharedWith(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'schedule_shares', 'schedule_id', 'shared_with_user_id')
                    ->withPivot('permission')
                    ->withTimestamps();
    }

    public function publicLinks(): HasMany
    {
        return $this->hasMany(PublicBookingLink::class);
    }

    // --------------------------------------------------------
    // Helpers
    // --------------------------------------------------------

    public function isSharedWith(User $user): bool
    {
        return $this->shares()->where('shared_with_user_id', $user->id)->exists();
    }

    public function permissionFor(User $user): ?string
    {
        return $this->shares()
                    ->where('shared_with_user_id', $user->id)
                    ->value('permission');
    }
}

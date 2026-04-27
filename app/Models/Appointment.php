<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'schedule_id', 'client_id',
        'client_name', 'client_email', 'client_phone',
        'title', 'description',
        'starts_at', 'ends_at', 'duration_minutes',
        'status',
        'google_event_id', 'outlook_event_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    // --------------------------------------------------------
    // Relações
    // --------------------------------------------------------

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // --------------------------------------------------------
    // Scopes
    // --------------------------------------------------------

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }

    public function scopeInRange($query, string $start, string $end)
    {
        return $query->whereBetween('starts_at', [$start, $end]);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    // --------------------------------------------------------
    // Helpers
    // --------------------------------------------------------

    public function resolvedClientName(): string
    {
        return $this->client?->name ?? $this->client_name ?? 'Cliente avulso';
    }

    public function isPast(): bool
    {
        return $this->ends_at->isPast();
    }
}

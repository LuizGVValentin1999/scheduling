<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PublicBookingLink extends Model
{
    protected $fillable = [
        'schedule_id', 'token', 'label', 'settings', 'is_active', 'expires_at',
    ];

    protected $casts = [
        'settings'   => 'array',
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $link) {
            $link->token ??= Str::random(48);
        });
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function isValid(): bool
    {
        return $this->is_active
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function getPublicUrlAttribute(): string
    {
        return route('public.book', $this->token);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarIntegration extends Model
{
    protected $fillable = [
        'user_id', 'tenant_id', 'provider',
        'access_token', 'refresh_token', 'token_expires_at',
        'calendar_id', 'is_active',
    ];

    // Tokens criptografados no banco — nunca expostos em texto plano
    protected $casts = [
        'access_token'     => 'encrypted',
        'refresh_token'    => 'encrypted',
        'token_expires_at' => 'datetime',
        'is_active'        => 'boolean',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->token_expires_at !== null
            && $this->token_expires_at->isPast();
    }
}

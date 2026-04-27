<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'email', 'phone', 'notes'];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}

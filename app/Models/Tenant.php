<?php

namespace App\Models;

use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    public function getNameAttribute(): ?string
    {
        return $this->attributes['display_name'] ?? $this->data['name'] ?? null;
    }

    public function getPrimaryDomainAttribute(): ?string
    {
        return $this->domains->first()?->domain;
    }
}

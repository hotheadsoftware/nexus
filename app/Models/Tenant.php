<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;


    /**
     * A brand is an entity to hold customizations to the panels in the tenant context.
     * This allows clients to specify logos, colors, and headlines for their businesses.
     *
     * A tenant (optionally) owns many brands - one per panel type. This allows custom
     * color schemes for each panel type.
     */
    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getCustomColumns(): array
    {
        return [
            'id' => 'id',
            // Used for Display purposes, but also to generate the initial default domain.
            'name' => 'name',
            // All tenants must be owned by users.
            'user_id' => 'user_id',

        ];
    }

}

<?php

namespace App\Models;

use OwenIt\Auditing\Auditable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase, \OwenIt\Auditing\Contracts\Auditable
{
    use HasDatabase, HasDomains, Auditable;
}

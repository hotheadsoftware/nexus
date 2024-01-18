<?php

namespace App\Console\Commands;

use App\Jobs\CreateTenantBrands;
use App\Models\Tenant;
use Illuminate\Console\Command;

class NexusSeedTenants extends Command
{
    protected $signature = 'nexus:seed-tenants';

    protected $description = 'Adds the new user type to the appropriate user seeder.';

    public function handle(): void
    {
        // Migrate & Seed all tenant databases. (NOT Fresh - we don't want data destruction here).
        $this->call('tenants:migrate');
        $this->call('tenants:seed');

        // Send a job to ensure that we have all panels covered by custom branding.
        Tenant::all()->map(function (Tenant $tenant) {
            CreateTenantBrands::dispatch($tenant);
        });
    }
}

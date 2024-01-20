<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

abstract class TenantAwareTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected Tenant|Collection|Model $tenant;

    /**
     * @throws TenantCouldNotBeIdentifiedById
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a new tenant
        $this->tenant = Tenant::factory()->create();

        // Set the tenant as the current tenant
        tenancy()->initialize($this->tenant);
    }

    protected function tearDown(): void
    {
        // Clean up the tenant
        $this->tenant->delete();

        parent::tearDown();
    }
}

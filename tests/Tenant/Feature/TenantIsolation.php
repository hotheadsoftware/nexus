<?php

use App\Models\Tenant;

test('data is isolated', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    tenancy()->initialize($tenant1);
    \Spatie\Permission\Models\Role::create(['name' => 'Tenant 1 Role']);
    $this->assertDatabaseHas('roles', ['name' => 'Tenant 1 Role']);
    $this->assertDatabaseMissing('roles', ['name' => 'Tenant 2 Role']);

    tenancy()->initialize($tenant2);
    \Spatie\Permission\Models\Role::create(['name' => 'Tenant 2 Role']);
    $this->assertDatabaseHas('roles', ['name' => 'Tenant 2 Role']);
    $this->assertDatabaseMissing('roles', ['name' => 'Tenant 1 Role']);

    tenancy()->central(function () use ($tenant1, $tenant2) {
        $tenant2->delete();
        $tenant1->delete();
    });
});

<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

test('can create a tenant', function () {
    $driverName = DB::connection()->getDriverName();
    $tenant     = Tenant::factory()->create();

    if ($driverName === 'pgsql') {
        $schemas       = DB::select('SELECT datname FROM pg_database');
        $databaseNames = array_map(function ($db) {
            return $db->datname;
        }, $schemas);
    } else {
        $schemas       = DB::select('SHOW DATABASES');
        $databaseNames = array_map(function ($db) {
            return $db->Database;
        }, $schemas);
    }

    expect($tenant->exists)->toBeTrue()
        ->and($tenant->domains->count())->toBe(1)
        ->and(in_array($tenant->tenancy_db_name, $databaseNames))->toBeTrue();

    // cleanup
    $tenant->delete();
});

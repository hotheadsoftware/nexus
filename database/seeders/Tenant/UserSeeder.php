<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;

// do-not-remove-this-nexus-anchor-user-seeder-use-statements

/**
 * This UserSeeder is managed programmatically by the Nexus artisan commands.
 * We leave placeholder anchor comments in the file so Nexus can quickly insert
 * the appropriate values as needed.
 *
 * If you modify or remove those comments, Nexus will no longer be able to create
 * new user types for your panels. You can safely modify the code between the
 * anchor comments.
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * We're only going to create this user in a local environment, but it will be created
         * with each tenant in that environment. This allows us to login to each tenant and
         * create users, etc. as needed. We'll use impersonation in prod where needed.
         */
        if (app()->environment() === 'local') {
            // do-not-remove-this-nexus-anchor-user-seeder-model-creation
        }
    }
}

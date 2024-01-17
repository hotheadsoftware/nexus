<?php

namespace Database\Seeders;

# do-not-remove-this-nexus-anchor-user-seeder-use-statements
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        // We're going to create an Administrator (platform operator) regardless of environment.
        // This user has access to the admin panel and can manage, monitor, and configure the
        // application, including maintenance mode, etc.

        Administrator::firstOrCreate([
            'email' => config('nexus.admin.user.email'),
        ], [
            'name'              => config('nexus.admin.user.name'),
            'email'             => config('nexus.admin.user.email'),
            'password'          => Hash::make(config('nexus.admin.user.password')),
            'email_verified_at' => now(),
        ]);

        if (app()->environment() === 'local') {

            // In local, we'll create one user for the 'account' panel to get it started.
            // In prod, we don't seed users. They register or are added from another panel.

            User::firstOrCreate([
                'email' => config('nexus.account.user.email'),
            ], [
                'name'              => config('nexus.account.user.name'),
                'email'             => config('nexus.account.user.email'),
                'password'          => Hash::make(config('nexus.account.user.password')),
                'email_verified_at' => now(),
            ]);

            # do-not-remove-this-nexus-anchor-user-seeder-model-creation
        }
    }
}

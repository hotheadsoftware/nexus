<?php

namespace Database\Seeders;

use App\Models\Administrator;
use App\Models\User;
# do-not-remove-this-nexus-anchor-user-seeder-use-statements
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
        User::firstOrCreate([
            'email' => config('panels.account.user.email'),
        ], [
            'name'              => config('panels.account.user.name'),
            'email'             => config('panels.account.user.email'),
            'password'          => Hash::make(config('panels.account.user.password')),
            'email_verified_at' => now(),
        ]);

        Administrator::firstOrCreate([
            'email' => config('panels.admin.user.email'),
        ], [
            'name'              => config('panels.admin.user.name'),
            'email'             => config('panels.admin.user.email'),
            'password'          => Hash::make(config('panels.admin.user.password')),
            'email_verified_at' => now(),
        ]);

        # do-not-remove-this-nexus-anchor-user-seeder-model-creation
    }
}

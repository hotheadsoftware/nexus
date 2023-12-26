<?php

namespace Database\Seeders\Tenant;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            User::create([
                'name' => config('app.app_plane.user.name'),
                'email' => config('app.app_plane.user.email'),
                'password' => Hash::make(config('app.app_plane.user.password')),
                'email_verified_at' => now(),
            ]);
        }
    }
}

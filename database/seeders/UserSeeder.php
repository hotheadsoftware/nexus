<?php

namespace Database\Seeders;

use App\Models\Administrator;
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
    }
}

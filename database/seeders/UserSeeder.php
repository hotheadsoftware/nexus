<?php

namespace Database\Seeders;

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
        User::create([
            'name' => config('app.control_plane.user.name'),
            'email' => config('app.control_plane.user.email'),
            'password' => Hash::make(config('app.control_plane.user.password')),
            'email_verified_at' => now(),
        ]);
    }
}

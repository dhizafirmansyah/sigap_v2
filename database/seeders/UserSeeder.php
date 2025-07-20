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
        // Super Admin
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@sigap.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        // Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@sigap.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Viewer
        User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@sigap.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'email_verified_at' => now(),
        ]);

        // Additional demo users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@sigap.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@sigap.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
            'email_verified_at' => now(),
        ]);
    }
}

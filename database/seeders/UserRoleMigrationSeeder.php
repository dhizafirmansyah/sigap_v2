<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, create a default user if none exists
        if (User::count() === 0) {
            $superadminRole = Role::where('name', 'superadmin')->first();
            
            User::create([
                'name' => 'Super Administrator',
                'email' => 'admin@sigap.com',
                'password' => bcrypt('password'),
                'role_id' => $superadminRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            echo "Created default superadmin user: admin@sigap.com / password\n";
        } else {
            // Migrate existing users - check if they still have the old role column
            $usersWithoutRoles = User::whereNull('role_id')->get();
            
            foreach ($usersWithoutRoles as $user) {
                // Assign superadmin role to first user, viewer to others
                $roleName = $user->id === 1 ? 'superadmin' : 'viewer';
                $role = Role::where('name', $roleName)->first();
                
                $user->update([
                    'role_id' => $role->id,
                    'is_active' => true,
                ]);
                
                echo "Assigned {$roleName} role to user: {$user->email}\n";
            }
        }

        echo "User role migration completed successfully!\n";
    }
}

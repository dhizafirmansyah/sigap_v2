<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // User Management
            ['name' => 'view_users', 'display_name' => 'View Users', 'description' => 'Can view user listings and details', 'group' => 'users'],
            ['name' => 'create_users', 'display_name' => 'Create Users', 'description' => 'Can create new users', 'group' => 'users'],
            ['name' => 'edit_users', 'display_name' => 'Edit Users', 'description' => 'Can edit user information', 'group' => 'users'],
            ['name' => 'delete_users', 'display_name' => 'Delete Users', 'description' => 'Can delete users', 'group' => 'users'],
            ['name' => 'manage_user_permissions', 'display_name' => 'Manage User Permissions', 'description' => 'Can assign/revoke permissions to users', 'group' => 'users'],

            // Employee Management
            ['name' => 'view_employees', 'display_name' => 'View Employees', 'description' => 'Can view employee listings and details', 'group' => 'employees'],
            ['name' => 'create_employees', 'display_name' => 'Create Employees', 'description' => 'Can create new employees', 'group' => 'employees'],
            ['name' => 'edit_employees', 'display_name' => 'Edit Employees', 'description' => 'Can edit employee information', 'group' => 'employees'],
            ['name' => 'delete_employees', 'display_name' => 'Delete Employees', 'description' => 'Can delete employees', 'group' => 'employees'],

            // Location Management
            ['name' => 'view_locations', 'display_name' => 'View Locations', 'description' => 'Can view location listings and details', 'group' => 'locations'],
            ['name' => 'create_locations', 'display_name' => 'Create Locations', 'description' => 'Can create new locations', 'group' => 'locations'],
            ['name' => 'edit_locations', 'display_name' => 'Edit Locations', 'description' => 'Can edit location information', 'group' => 'locations'],
            ['name' => 'delete_locations', 'display_name' => 'Delete Locations', 'description' => 'Can delete locations', 'group' => 'locations'],

            // Brand Management
            ['name' => 'view_brands', 'display_name' => 'View Brands', 'description' => 'Can view brand listings and details', 'group' => 'brands'],
            ['name' => 'create_brands', 'display_name' => 'Create Brands', 'description' => 'Can create new brands', 'group' => 'brands'],
            ['name' => 'edit_brands', 'display_name' => 'Edit Brands', 'description' => 'Can edit brand information', 'group' => 'brands'],
            ['name' => 'delete_brands', 'display_name' => 'Delete Brands', 'description' => 'Can delete brands', 'group' => 'brands'],

            // Shift Management
            ['name' => 'view_shifts', 'display_name' => 'View Shifts', 'description' => 'Can view shift schedules and details', 'group' => 'shifts'],
            ['name' => 'create_shifts', 'display_name' => 'Create Shifts', 'description' => 'Can create new shifts', 'group' => 'shifts'],
            ['name' => 'edit_shifts', 'display_name' => 'Edit Shifts', 'description' => 'Can edit shift information', 'group' => 'shifts'],
            ['name' => 'delete_shifts', 'display_name' => 'Delete Shifts', 'description' => 'Can delete shifts', 'group' => 'shifts'],

            // Presence Management
            ['name' => 'view_presences', 'display_name' => 'View Presences', 'description' => 'Can view attendance records', 'group' => 'presences'],
            ['name' => 'create_presences', 'display_name' => 'Create Presences', 'description' => 'Can record attendance', 'group' => 'presences'],
            ['name' => 'edit_presences', 'display_name' => 'Edit Presences', 'description' => 'Can edit attendance records', 'group' => 'presences'],
            ['name' => 'delete_presences', 'display_name' => 'Delete Presences', 'description' => 'Can delete attendance records', 'group' => 'presences'],

            // Quality Management
            ['name' => 'view_quality', 'display_name' => 'View Quality Records', 'description' => 'Can view quality metrics and records', 'group' => 'quality'],
            ['name' => 'create_quality', 'display_name' => 'Create Quality Records', 'description' => 'Can create quality records', 'group' => 'quality'],
            ['name' => 'edit_quality', 'display_name' => 'Edit Quality Records', 'description' => 'Can edit quality records', 'group' => 'quality'],
            ['name' => 'delete_quality', 'display_name' => 'Delete Quality Records', 'description' => 'Can delete quality records', 'group' => 'quality'],

            // Pack Management
            ['name' => 'view_packs', 'display_name' => 'View Packs', 'description' => 'Can view pack records', 'group' => 'packs'],
            ['name' => 'create_packs', 'display_name' => 'Create Packs', 'description' => 'Can create pack records', 'group' => 'packs'],
            ['name' => 'edit_packs', 'display_name' => 'Edit Packs', 'description' => 'Can edit pack records', 'group' => 'packs'],
            ['name' => 'delete_packs', 'display_name' => 'Delete Packs', 'description' => 'Can delete pack records', 'group' => 'packs'],

            // Contract Management
            ['name' => 'view_contracts', 'display_name' => 'View Contracts', 'description' => 'Can view employment contracts', 'group' => 'contracts'],
            ['name' => 'create_contracts', 'display_name' => 'Create Contracts', 'description' => 'Can create employment contracts', 'group' => 'contracts'],
            ['name' => 'edit_contracts', 'display_name' => 'Edit Contracts', 'description' => 'Can edit employment contracts', 'group' => 'contracts'],
            ['name' => 'delete_contracts', 'display_name' => 'Delete Contracts', 'description' => 'Can delete employment contracts', 'group' => 'contracts'],

            // Reports and Analytics
            ['name' => 'view_reports', 'display_name' => 'View Reports', 'description' => 'Can view system reports and analytics', 'group' => 'reports'],
            ['name' => 'export_data', 'display_name' => 'Export Data', 'description' => 'Can export data in various formats', 'group' => 'reports'],

            // System Administration
            ['name' => 'system_settings', 'display_name' => 'System Settings', 'description' => 'Can access and modify system settings', 'group' => 'system'],
            ['name' => 'audit_logs', 'display_name' => 'Audit Logs', 'description' => 'Can view system audit logs', 'group' => 'system'],
            ['name' => 'backup_restore', 'display_name' => 'Backup & Restore', 'description' => 'Can perform system backup and restore', 'group' => 'system'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create Roles
        $roles = [
            [
                'name' => 'superadmin',
                'display_name' => 'Super Administrator',
                'description' => 'Has access to all system functions',
                'level' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Has access to most system functions',
                'level' => 80,
                'is_active' => true,
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Can manage employees and operations',
                'level' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'supervisor',
                'display_name' => 'Supervisor',
                'description' => 'Can supervise daily operations',
                'level' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Can only view data',
                'level' => 20,
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            // Assign permissions based on role
            switch ($roleData['name']) {
                case 'superadmin':
                    // SuperAdmin gets all permissions
                    $role->permissions()->sync(Permission::all()->pluck('id'));
                    break;

                case 'admin':
                    // Admin gets most permissions except system administration
                    $adminPermissions = Permission::whereNotIn('group', ['system'])->pluck('id');
                    $role->permissions()->sync($adminPermissions);
                    break;

                case 'manager':
                    // Manager can manage employees, locations, brands, shifts, and view reports
                    $managerPermissions = Permission::whereIn('group', [
                        'employees', 'locations', 'brands', 'shifts', 'presences', 'quality', 'packs', 'contracts', 'reports'
                    ])->whereNotIn('name', ['delete_employees', 'delete_locations', 'delete_brands', 'backup_restore'])
                    ->pluck('id');
                    $role->permissions()->sync($managerPermissions);
                    break;

                case 'supervisor':
                    // Supervisor can view and edit daily operations
                    $supervisorPermissions = Permission::whereIn('group', [
                        'employees', 'presences', 'quality', 'packs', 'shifts'
                    ])->whereNotIn('name', [
                        'delete_employees', 'create_employees', 'edit_employees',
                        'delete_shifts', 'create_shifts', 'edit_shifts'
                    ])->pluck('id');
                    $role->permissions()->sync($supervisorPermissions);
                    break;

                case 'viewer':
                    // Viewer can only view data
                    $viewerPermissions = Permission::where('name', 'like', 'view_%')->pluck('id');
                    $role->permissions()->sync($viewerPermissions);
                    break;
            }
        }

        echo "Permissions and roles seeded successfully!\n";
    }
}

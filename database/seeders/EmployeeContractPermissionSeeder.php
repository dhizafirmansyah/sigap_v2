<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeContractPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Employee Contract permissions
        $permissions = [
            ['name' => 'view_employee_contracts', 'display_name' => 'View Employee Contracts', 'description' => 'Can view employee contract listings and details', 'group' => 'employee_contracts'],
            ['name' => 'create_employee_contracts', 'display_name' => 'Create Employee Contracts', 'description' => 'Can create new employee contracts', 'group' => 'employee_contracts'],
            ['name' => 'edit_employee_contracts', 'display_name' => 'Edit Employee Contracts', 'description' => 'Can edit employee contract information', 'group' => 'employee_contracts'],
            ['name' => 'delete_employee_contracts', 'display_name' => 'Delete Employee Contracts', 'description' => 'Can delete employee contracts', 'group' => 'employee_contracts'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        echo "Employee Contract permissions seeded successfully!\n";
    }

    private function assignPermissionsToRoles()
    {
        // Get all employee contract permissions
        $contractPermissions = Permission::where('group', 'employee_contracts')->pluck('id');

        // Assign to SuperAdmin (gets all permissions)
        $superadmin = Role::where('name', 'superadmin')->first();
        if ($superadmin) {
            $superadmin->permissions()->syncWithoutDetaching($contractPermissions);
        }

        // Assign to Admin (gets all except delete for sensitive operations)
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $adminPermissions = Permission::where('group', 'employee_contracts')
                ->whereNotIn('name', ['delete_employee_contracts'])
                ->pluck('id');
            $admin->permissions()->syncWithoutDetaching($adminPermissions);
        }

        // Assign to Manager (gets view, create, edit)
        $manager = Role::where('name', 'manager')->first();
        if ($manager) {
            $managerPermissions = Permission::where('group', 'employee_contracts')
                ->whereIn('name', ['view_employee_contracts', 'create_employee_contracts', 'edit_employee_contracts'])
                ->pluck('id');
            $manager->permissions()->syncWithoutDetaching($managerPermissions);
        }

        // Assign to Supervisor (gets view only)
        $supervisor = Role::where('name', 'supervisor')->first();
        if ($supervisor) {
            $supervisorPermissions = Permission::where('group', 'employee_contracts')
                ->where('name', 'view_employee_contracts')
                ->pluck('id');
            $supervisor->permissions()->syncWithoutDetaching($supervisorPermissions);
        }
    }
}

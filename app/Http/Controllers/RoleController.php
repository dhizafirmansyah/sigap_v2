<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use App\Traits\HasPermissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoleController extends Controller
{
    use HasPermissions;

    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display role management dashboard.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('view_users');

        $filters = $request->only(['search', 'is_active', 'level_min', 'level_max', 'sort_by', 'sort_order', 'per_page']);
        
        $roles = $this->roleService->getPaginatedRoles($filters);
        $statistics = $this->roleService->getRoleStatistics();
        $permissionGroups = $this->roleService->getPermissionsByGroup();

        return Inertia::render('Role/RoleManagement', [
            'roles' => $roles,
            'statistics' => $statistics,
            'permissionGroups' => $permissionGroups,
            'filters' => $filters,
            'user' => $this->getUserWithPermissions(),
        ]);
    }

    /**
     * Show role details.
     */
    public function show(Role $role, Request $request)
    {
        $this->authorizePermission('view_users');

        $role->load(['permissions', 'users']);
        
        $filters = $request->only(['search', 'is_active', 'sort_by', 'sort_order', 'per_page']);
        $users = $this->roleService->getUsersByRole($role, $filters);
        $permissionGroups = $this->roleService->getPermissionsByGroup();

        return Inertia::render('Role/RoleDetail', [
            'role' => $role,
            'users' => $users,
            'permissionGroups' => $permissionGroups,
            'filters' => $filters,
        ]);
    }

    /**
     * Store a new role.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('create_users');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'level' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $role = $this->roleService->createRole(
                $validated,
                $validated['permissions'] ?? []
            );

            return response()->json([
                'message' => 'Role created successfully',
                'role' => $role,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a role.
     */
    public function update(Request $request, Role $role)
    {
        $this->authorizePermission('edit_users');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'level' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $updatedRole = $this->roleService->updateRole(
                $role,
                $validated,
                $validated['permissions'] ?? []
            );

            return response()->json([
                'message' => 'Role updated successfully',
                'role' => $updatedRole,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a role.
     */
    public function destroy(Role $role)
    {
        $this->authorizePermission('delete_users');

        try {
            $this->roleService->deleteRole($role);

            return response()->json([
                'message' => 'Role deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete role',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Duplicate a role.
     */
    public function duplicate(Request $request, Role $role)
    {
        $this->authorizePermission('create_users');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
        ]);

        try {
            $newRole = $this->roleService->duplicateRole(
                $role,
                $validated['name'],
                $validated['display_name']
            );

            return response()->json([
                'message' => 'Role duplicated successfully',
                'role' => $newRole,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to duplicate role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign role to user.
     */
    public function assignUser(Request $request, Role $role)
    {
        $this->authorizePermission('edit_users');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $user = User::findOrFail($validated['user_id']);
            $this->roleService->assignRoleToUser($user, $role);

            return response()->json([
                'message' => 'Role assigned to user successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to assign role to user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove role from user.
     */
    public function removeUser(Request $request, Role $role)
    {
        $this->authorizePermission('edit_users');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $user = User::findOrFail($validated['user_id']);
            $this->roleService->removeRoleFromUser($user);

            return response()->json([
                'message' => 'Role removed from user successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove role from user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get role hierarchy for dropdowns.
     */
    public function hierarchy()
    {
        $this->authorizePermission('view_users');

        $roles = $this->roleService->getRoleHierarchy();

        return response()->json([
            'roles' => $roles,
        ]);
    }

    /**
     * Get role statistics.
     */
    public function statistics()
    {
        $this->authorizePermission('view_users');

        $statistics = $this->roleService->getRoleStatistics();

        return response()->json([
            'statistics' => $statistics,
        ]);
    }
}

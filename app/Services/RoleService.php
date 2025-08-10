<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleService
{
    /**
     * Get paginated roles with permissions count.
     */
    public function getPaginatedRoles(array $filters = []): LengthAwarePaginator
    {
        $query = Role::with(['permissions', 'users'])
            ->withCount(['permissions', 'users']);

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['level_min'])) {
            $query->where('level', '>=', $filters['level_min']);
        }

        if (!empty($filters['level_max'])) {
            $query->where('level', '<=', $filters['level_max']);
        }

        // Sort by level descending by default
        $sortBy = $filters['sort_by'] ?? 'level';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    /**
     * Get all permissions grouped by group.
     */
    public function getPermissionsByGroup(): Collection
    {
        return Permission::active()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');
    }

    /**
     * Get all permissions for a specific role.
     */
    public function getRolePermissions(Role $role): Collection
    {
        return $role->permissions()->active()->get();
    }

    /**
     * Create a new role with permissions.
     */
    public function createRole(array $data, array $permissions = []): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
            'level' => $data['level'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (!empty($permissions)) {
            $role->permissions()->sync($permissions);
        }

        return $role->load(['permissions', 'users']);
    }

    /**
     * Update a role with permissions.
     */
    public function updateRole(Role $role, array $data, array $permissions = []): Role
    {
        $role->update([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
            'level' => $data['level'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        $role->permissions()->sync($permissions);

        return $role->fresh(['permissions', 'users']);
    }

    /**
     * Delete a role.
     */
    public function deleteRole(Role $role): bool
    {
        // Check if role has users
        if ($role->users()->count() > 0) {
            throw new \Exception('Cannot delete role that has assigned users. Please reassign users first.');
        }

        // Detach all permissions
        $role->permissions()->detach();

        return $role->delete();
    }

    /**
     * Get role statistics.
     */
    public function getRoleStatistics(): array
    {
        return [
            'total_roles' => Role::count(),
            'active_roles' => Role::where('is_active', true)->count(),
            'inactive_roles' => Role::where('is_active', false)->count(),
            'total_permissions' => Permission::count(),
            'active_permissions' => Permission::where('is_active', true)->count(),
            'permission_groups' => Permission::distinct('group')->count('group'),
            'users_with_roles' => User::whereNotNull('role_id')->count(),
            'users_without_roles' => User::whereNull('role_id')->count(),
        ];
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(Role $role, array $filters = []): LengthAwarePaginator
    {
        $query = $role->users()->with(['role']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    /**
     * Assign role to user.
     */
    public function assignRoleToUser(User $user, Role $role): User
    {
        $user->update(['role_id' => $role->id]);
        return $user->fresh(['role']);
    }

    /**
     * Remove role from user.
     */
    public function removeRoleFromUser(User $user): User
    {
        $user->update(['role_id' => null]);
        return $user->fresh(['role']);
    }

    /**
     * Duplicate a role with new name.
     */
    public function duplicateRole(Role $sourceRole, string $newName, string $newDisplayName): Role
    {
        $newRole = Role::create([
            'name' => $newName,
            'display_name' => $newDisplayName,
            'description' => $sourceRole->description . ' (Copy)',
            'level' => $sourceRole->level,
            'is_active' => true,
        ]);

        // Copy all permissions
        $permissionIds = $sourceRole->permissions()->pluck('permissions.id');
        $newRole->permissions()->sync($permissionIds);

        return $newRole->load(['permissions', 'users']);
    }

    /**
     * Get role hierarchy for dropdown/selection.
     */
    public function getRoleHierarchy(): Collection
    {
        return Role::active()
            ->orderBy('level', 'desc')
            ->select(['id', 'name', 'display_name', 'level'])
            ->get();
    }
}

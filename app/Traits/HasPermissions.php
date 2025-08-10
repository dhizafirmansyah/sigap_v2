<?php

namespace App\Traits;

trait HasPermissions
{
    /**
     * Check if the authenticated user has a specific permission.
     */
    protected function hasPermission(string $permission): bool
    {
        return auth()->check() && auth()->user()->hasPermission($permission);
    }

    /**
     * Check if the authenticated user has any of the given permissions.
     */
    protected function hasAnyPermission(array $permissions): bool
    {
        return auth()->check() && auth()->user()->hasAnyPermission($permissions);
    }

    /**
     * Check if the authenticated user has all of the given permissions.
     */
    protected function hasAllPermissions(array $permissions): bool
    {
        return auth()->check() && auth()->user()->hasAllPermissions($permissions);
    }

    /**
     * Authorize permission or throw 403 error.
     */
    protected function authorizePermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            abort(403, "You do not have permission: {$permission}");
        }
    }

    /**
     * Authorize any of the permissions or throw 403 error.
     */
    protected function authorizeAnyPermission(array $permissions): void
    {
        if (!$this->hasAnyPermission($permissions)) {
            $permissionList = implode(', ', $permissions);
            abort(403, "You do not have any of these permissions: {$permissionList}");
        }
    }

    /**
     * Authorize all permissions or throw 403 error.
     */
    protected function authorizeAllPermissions(array $permissions): void
    {
        if (!$this->hasAllPermissions($permissions)) {
            $permissionList = implode(', ', $permissions);
            abort(403, "You do not have all required permissions: {$permissionList}");
        }
    }

    /**
     * Get all permissions for the authenticated user.
     */
    protected function getUserPermissions()
    {
        return auth()->check() ? auth()->user()->getAllPermissions() : collect();
    }

    /**
     * Check if user has permission and return user data for frontend.
     */
    protected function getUserWithPermissions()
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();
        $user->load(['role', 'permissions']);
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'name' => $user->role->name,
                'display_name' => $user->role->display_name,
                'level' => $user->role->level,
            ] : null,
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'role_permissions' => $user->role ? $user->role->permissions->pluck('name')->toArray() : [],
            'direct_permissions' => $user->permissions->pluck('name')->toArray(),
        ];
    }
}

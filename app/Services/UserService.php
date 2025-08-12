<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get paginated users with role and permissions count.
     */
    public function getPaginatedUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['role', 'permissions'])
            ->withCount(['permissions']);

        // Apply filters
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

        if (!empty($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        // Sort by name ascending by default
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $filters['per_page'] ?? 10;
        
        return $query->paginate($perPage);
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = User::create($data);

        // Sync permissions if provided
        if (isset($data['permissions'])) {
            $user->permissions()->sync($data['permissions']);
        }

        return $user->load(['role', 'permissions']);
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): User
    {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Remove password from data if empty to avoid updating
            unset($data['password']);
        }

        $user->update($data);

        // Sync permissions if provided
        if (isset($data['permissions'])) {
            $user->permissions()->sync($data['permissions']);
        }

        return $user->load(['role', 'permissions']);
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user): bool
    {
        // Detach all permissions before deleting
        $user->permissions()->detach();
        
        return $user->delete();
    }

    /**
     * Get user statistics.
     */
    public function getUserStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'users_with_roles' => User::whereNotNull('role_id')->count(),
            'users_without_roles' => User::whereNull('role_id')->count(),
        ];
    }

    /**
     * Get all roles for dropdowns.
     */
    public function getAllRoles(): Collection
    {
        return Role::where('is_active', true)
            ->orderBy('level', 'desc')
            ->get(['id', 'name', 'display_name', 'level']);
    }

    /**
     * Assign role to user.
     */
    public function assignRole(User $user, int $roleId): User
    {
        $user->update(['role_id' => $roleId]);
        return $user->load(['role', 'permissions']);
    }

    /**
     * Remove role from user.
     */
    public function removeRole(User $user): User
    {
        $user->update(['role_id' => null]);
        return $user->load(['role', 'permissions']);
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        return $user;
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user, string $newPassword): User
    {
        $user->update(['password' => Hash::make($newPassword)]);
        return $user;
    }

    /**
     * Get user with detailed information.
     */
    public function getUserDetails(User $user): User
    {
        return $user->load([
            'role' => function ($query) {
                $query->with('permissions');
            },
            'permissions'
        ]);
    }
}

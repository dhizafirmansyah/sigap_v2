<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Services\RoleService;
use App\Traits\HasPermissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    use HasPermissions;

    protected $userService;
    protected $roleService;

    public function __construct(UserService $userService, RoleService $roleService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    /**
     * Display user management dashboard.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('view_users');

        $filters = $request->only(['search', 'is_active', 'role_id', 'sort_by', 'sort_order', 'per_page']);
        
        $users = $this->userService->getPaginatedUsers($filters);
        $statistics = $this->userService->getUserStatistics();
        $roles = $this->userService->getAllRoles();
        $permissionGroups = $this->roleService->getPermissionsByGroup();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'statistics' => $statistics,
            'roles' => $roles,
            'permissionGroups' => $permissionGroups,
            'filters' => $filters,
            'user' => $this->getUserWithPermissions(),
        ]);
    }

    /**
     * Show user details.
     */
    public function show(User $user, Request $request)
    {
        $this->authorizePermission('view_users');

        $userDetails = $this->userService->getUserDetails($user);

        return Inertia::render('Users/UserDetail', [
            'user' => $userDetails,
            'canEdit' => $this->hasPermission('edit_users'),
            'canDelete' => $this->hasPermission('delete_users'),
        ]);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('create_users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $user = $this->userService->createUser($validated);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizePermission('edit_users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $updatedUser = $this->userService->updateUser($user, $validated);

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        $this->authorizePermission('delete_users');

        // Prevent deletion of current user
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->withErrors(['error' => 'You cannot delete your own account.']);
        }

        try {
            $this->userService->deleteUser($user);

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Request $request, User $user)
    {
        $this->authorizePermission('edit_users');

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        try {
            $this->userService->assignRole($user, $validated['role_id']);

            return redirect()->back()
                ->with('success', 'Role assigned successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to assign role: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove role from user.
     */
    public function removeRole(User $user)
    {
        $this->authorizePermission('edit_users');

        try {
            $this->userService->removeRole($user);

            return redirect()->back()
                ->with('success', 'Role removed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to remove role: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        $this->authorizePermission('edit_users');

        // Prevent deactivating current user
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        try {
            $this->userService->toggleStatus($user);

            $status = $user->is_active ? 'activated' : 'deactivated';
            return redirect()->back()
                ->with('success', "User {$status} successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to toggle user status: ' . $e->getMessage()]);
        }
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorizePermission('edit_users');

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->userService->resetPassword($user, $validated['password']);

            return redirect()->back()
                ->with('success', 'Password reset successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to reset password: ' . $e->getMessage()]);
        }
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the role of the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the permissions assigned directly to the user.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot(['granted', 'granted_at', 'granted_by'])
            ->withTimestamps();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permission): bool
    {
        // Check if user is active
        if (!$this->is_active) {
            return false;
        }

        $permissionName = is_string($permission) ? $permission : $permission->name;

        // Check role permissions first
        if ($this->role && $this->role->is_active) {
            $roleHasPermission = $this->role->permissions()
                ->where('name', $permissionName)
                ->where('is_active', true)
                ->exists();

            if ($roleHasPermission) {
                // Check if this permission is explicitly revoked for the user
                $explicitRevoke = $this->permissions()
                    ->where('name', $permissionName)
                    ->wherePivot('granted', false)
                    ->exists();

                if (!$explicitRevoke) {
                    return true;
                }
            }
        }

        // Check direct user permissions
        return $this->permissions()
            ->where('name', $permissionName)
            ->where('is_active', true)
            ->wherePivot('granted', true)
            ->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Give permission to user.
     */
    public function givePermissionTo($permission, $grantedBy = null): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->syncWithoutDetaching([
                $permission->id => [
                    'granted' => true,
                    'granted_at' => now(),
                    'granted_by' => $grantedBy,
                ]
            ]);
        }
    }

    /**
     * Revoke permission from user.
     */
    public function revokePermissionTo($permission, $revokedBy = null): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->syncWithoutDetaching([
                $permission->id => [
                    'granted' => false,
                    'granted_at' => now(),
                    'granted_by' => $revokedBy,
                ]
            ]);
        }
    }

    /**
     * Get all permissions for this user (role + direct).
     */
    public function getAllPermissions()
    {
        $rolePermissions = collect();
        $directPermissions = $this->permissions()
            ->where('is_active', true)
            ->wherePivot('granted', true)
            ->get();

        if ($this->role && $this->role->is_active) {
            $rolePermissions = $this->role->permissions()
                ->where('is_active', true)
                ->get();
        }

        // Filter out role permissions that are explicitly revoked
        $revokedPermissions = $this->permissions()
            ->wherePivot('granted', false)
            ->pluck('name');

        $rolePermissions = $rolePermissions->filter(function ($permission) use ($revokedPermissions) {
            return !$revokedPermissions->contains($permission->name);
        });

        return $rolePermissions->merge($directPermissions)->unique('id');
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by role.
     */
    public function scopeByRole($query, $role)
    {
        if (is_string($role)) {
            return $query->whereHas('role', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        return $query->where('role_id', $role->id);
    }
}

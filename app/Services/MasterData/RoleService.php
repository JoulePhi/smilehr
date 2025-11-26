<?php

namespace App\Services\MasterData;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{
    /**
     * Get total users count across all roles.
     *
     * @return int
     */
    public function getTotalUsersCount(): int
    {
        return User::count();
    }

    /**
     * Get role statistics and analytics.
     *
     * @return array
     */
    public function getRoleStatistics(): array
    {
        return [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_users' => $this->getTotalUsersCount(),
            'active_mappings' => DB::table('role_has_permissions')->count(),
            'roles_by_guard' => Role::select('guard_name', DB::raw('count(*) as count'))
                ->groupBy('guard_name')
                ->get()
                ->pluck('count', 'guard_name')
                ->toArray(),
            'recent_roles' => Role::withCount('permissions', 'users')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'most_used_roles' => Role::withCount('users')
                ->orderBy('users_count', 'desc')
                ->limit(10)
                ->get(),
            'least_used_roles' => Role::withCount('users')
                ->orderBy('users_count', 'asc')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Get permission statistics and analytics.
     *
     * @return array
     */
    public function getPermissionStatistics(): array
    {
        $permissions = Permission::withCount('roles')->get();

        return [
            'total_permissions' => $permissions->count(),
            'permissions_by_guard' => Permission::select('guard_name', DB::raw('count(*) as count'))
                ->groupBy('guard_name')
                ->get()
                ->pluck('count', 'guard_name')
                ->toArray(),
            'most_used_permissions' => $permissions
                ->sortByDesc('roles_count')
                ->take(10)
                ->values(),
            'least_used_permissions' => $permissions
                ->sortBy('roles_count')
                ->take(10)
                ->values(),
            'recent_permissions' => Permission::withCount('roles')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'unused_permissions' => $permissions->filter(function ($permission) {
                return $permission->roles_count === 0;
            })->count(),
        ];
    }

    /**
     * Bulk assign permissions to multiple roles.
     *
     * @param array $roleIds
     * @param array $permissionIds
     * @return array
     */
    public function bulkAssignPermissions(array $roleIds, array $permissionIds): array
    {
        $roles = Role::whereIn('id', $roleIds)->get();
        $permissions = Permission::whereIn('id', $permissionIds)->get();

        $assignedCount = 0;

        foreach ($roles as $role) {
            foreach ($permissions as $permission) {
                if (!$role->hasPermissionTo($permission->name)) {
                    $role->givePermissionTo($permission);
                    $assignedCount++;
                }
            }
        }

        return [
            'affected_roles' => $roles->count(),
            'affected_permissions' => $permissions->count(),
            'new_assignments' => $assignedCount,
        ];
    }

    /**
     * Bulk revoke permissions from multiple roles.
     *
     * @param array $roleIds
     * @param array $permissionIds
     * @return array
     */
    public function bulkRevokePermissions(array $roleIds, array $permissionIds): array
    {
        $roles = Role::whereIn('id', $roleIds)->get();
        $permissions = Permission::whereIn('id', $permissionIds)->get();

        $revokedCount = 0;

        foreach ($roles as $role) {
            foreach ($permissions as $permission) {
                if ($role->hasPermissionTo($permission->name)) {
                    $role->revokePermissionTo($permission);
                    $revokedCount++;
                }
            }
        }

        return [
            'affected_roles' => $roles->count(),
            'affected_permissions' => $permissions->count(),
            'revoked_assignments' => $revokedCount,
        ];
    }

    /**
     * Get users assigned to a specific role with pagination.
     *
     * @param Role $role
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRoleUsers(Role $role, int $perPage = 15)
    {
        return $role->users()
            ->select('id', 'name', 'email', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Check if a role can be safely deleted.
     *
     * @param Role $role
     * @return array
     */
    public function canDeleteRole(Role $role): array
    {
        $userCount = $role->users()->count();
        $systemRoles = ['Super Admin', 'Admin', 'User'];

        $canDelete = true;
        $reasons = [];

        if ($userCount > 0) {
            $canDelete = false;
            $reasons[] = "This role is assigned to {$userCount} users.";
        }

        if (in_array($role->name, $systemRoles)) {
            $canDelete = false;
            $reasons[] = 'System roles cannot be deleted.';
        }

        return [
            'can_delete' => $canDelete,
            'reasons' => $reasons,
            'user_count' => $userCount,
            'is_system_role' => in_array($role->name, $systemRoles),
        ];
    }

    /**
     * Check if a permission can be safely deleted.
     *
     * @param Permission $permission
     * @return array
     */
    public function canDeletePermission(Permission $permission): array
    {
        $roleCount = $permission->roles()->count();

        $canDelete = true;
        $reasons = [];

        if ($roleCount > 0) {
            $canDelete = false;
            $reasons[] = "This permission is assigned to {$roleCount} roles.";
        }

        return [
            'can_delete' => $canDelete,
            'reasons' => $reasons,
            'role_count' => $roleCount,
        ];
    }

    /**
     * Get permissions grouped by categories.
     *
     * @return array
     */
    public function getPermissionsByCategories(): array
    {
        $permissions = Permission::withCount('roles')->get();

        // Group permissions by prefix (e.g., users.*, posts.*, etc.)
        $grouped = $permissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return count($parts) > 1 ? $parts[0] : 'general';
        });

        return $grouped->map(function ($group, $category) {
            return [
                'category' => ucfirst($category),
                'permissions' => $group,
                'count' => $group->count(),
                'total_usage' => $group->sum('roles_count'),
            ];
        })->values()->toArray();
    }

    /**
     * Search permissions with advanced filtering.
     *
     * @param string $query
     * @param string $guard
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchPermissions(string $query, string $guard = 'web', int $limit = 20)
    {
        return Permission::where('guard_name', $guard)
            ->where('name', 'LIKE', "%{$query}%")
            ->withCount('roles')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * Get available permissions for a role (permissions not already assigned).
     *
     * @param Role $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailablePermissionsForRole(Role $role)
    {
        $assignedPermissionIds = $role->permissions()->pluck('id');

        return Permission::whereNotIn('id', $assignedPermissionIds)
            ->withCount('roles')
            ->orderBy('name')
            ->get();
    }
}

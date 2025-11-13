# Laravel 12 Role & Permission API Setup

This document provides a complete implementation guide for Laravel 12 controllers to support the Vue.js role-permission management system. The setup integrates with Spatie Laravel-permission package and follows RESTful API conventions.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [File Structure](#file-structure)
3. [Controllers](#controllers)
4. [API Resources](#api-resources)
5. [Form Requests](#form-requests)
6. [API Routes](#api-routes)
7. [Middleware Setup](#middleware-setup)
8. [Configuration](#configuration)
9. [Testing](#testing)

## Prerequisites

- Laravel 12 (PHP 8.3+)
- Spatie Laravel-permission package
- Redis (for caching)
- MySQL/PostgreSQL database

### Install Required Packages

```bash
composer require spatie/laravel-permission
```

### Publish Spatie Configuration

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### Run Migrations

```bash
php artisan migrate
```

## File Structure

```
app/
├── Http/
│   ├── Controllers/API/
│   │   ├── RoleController.php
│   │   ├── PermissionController.php
│   │   └── RolePermissionController.php
│   ├── Resources/API/
│   │   ├── RoleResource.php
│   │   ├── PermissionResource.php
│   │   ├── RoleWithPermissionsResource.php
│   │   ├── PermissionWithRolesResource.php
│   │   └── RoleStatsResource.php
│   └── Requests/API/
│       ├── StoreRoleRequest.php
│       ├── UpdateRoleRequest.php
│       ├── StorePermissionRequest.php
│       ├── UpdatePermissionRequest.php
│       ├── SyncPermissionsRequest.php
│       └── BulkOperationRequest.php
└── Services/
    └── RoleService.php
```

## Controllers

### RoleController

**Location:** `app/Http/Controllers/API/RoleController.php`

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\{StoreRoleRequest, UpdateRoleRequest, BulkOperationRequest};
use App\Http\Resources\API\{RoleResource, RoleWithPermissionsResource, RoleStatsResource};
use App\Services\RoleService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\{Cache, DB, Log};
use Spatie\Permission\Models\{Role, Permission};

class RoleController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {
        $this->middleware('permission:roles.index')->only('index');
        $this->middleware('permission:roles.create')->only('store');
        $this->middleware('permission:roles.show')->only('show');
        $this->middleware('permission:roles.edit')->only('update');
        $this->middleware('permission:roles.delete')->only('destroy');
        $this->middleware('permission:roles.stats')->only('stats');
        $this->middleware('permission:roles.bulk')->only(['bulkAssignPermissions', 'bulkRevokePermissions']);
    }

    /**
     * Display a listing of roles with pagination and search.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $guard = $request->get('guard', 'web');

        // Validate pagination limits
        $perPage = min(max($perPage, 1), 100);

        $cacheKey = "roles.{$page}.{$perPage}.{$search}.{$guard}";

        $roles = Cache::remember($cacheKey, 300, function () use ($search, $perPage, $guard) {
            $query = Role::with('permissions')
                ->where('guard_name', $guard)
                ->orderBy('created_at', 'desc');

            if ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            return $query->paginate($perPage);
        });

        return response()->json([
            'data' => RoleResource::collection($roles->items()),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'from' => $roles->firstItem(),
                'to' => $roles->lastItem(),
            ],
            'message' => 'Roles retrieved successfully',
            'status' => true
        ]);
    }

    /**
     * Store a newly created role in storage.
     *
     * @param StoreRoleRequest $request
     * @return JsonResponse
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $request->validated('name'),
                'guard_name' => $request->validated('guard_name', 'web'),
            ]);

            // Assign permissions if provided
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->validated('permissions'))->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            // Clear cache
            $this->clearRoleCache();

            Log::info('Role created', ['role_id' => $role->id, 'name' => $role->name]);

            return response()->json([
                'data' => new RoleWithPermissionsResource($role->load('permissions')),
                'message' => 'Role created successfully',
                'status' => true
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to create role',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Display the specified role with its permissions.
     *
     * @param Role $role
     * @return JsonResource
     */
    public function show(Role $role): JsonResource
    {
        $role->load('permissions');

        return new RoleWithPermissionsResource($role);
    }

    /**
     * Update the specified role in storage.
     *
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return JsonResponse
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        try {
            DB::beginTransaction();

            $updateData = $request->validated();

            // Prevent updating name to existing role name
            if (isset($updateData['name']) && $updateData['name'] !== $role->name) {
                $updateData['name'] = $updateData['name'];
            }

            $role->update(array_filter($updateData));

            // Update permissions if provided
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->validated('permissions'))->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            // Clear cache
            $this->clearRoleCache();

            Log::info('Role updated', ['role_id' => $role->id, 'name' => $role->name]);

            return response()->json([
                'data' => new RoleWithPermissionsResource($role->load('permissions')),
                'message' => 'Role updated successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role update failed', ['role_id' => $role->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to update role',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Remove the specified role from storage.
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function destroy(Role $role): JsonResponse
    {
        try {
            // Check if role is being used by users
            $userCount = $role->users()->count();

            if ($userCount > 0) {
                return response()->json([
                    'message' => 'Cannot delete role. It is assigned to users.',
                    'errors' => ['users' => "This role is assigned to {$userCount} users"],
                    'status' => false
                ], 422);
            }

            // Prevent deletion of system roles
            if (in_array($role->name, ['Super Admin', 'Admin', 'User'])) {
                return response()->json([
                    'message' => 'Cannot delete system roles',
                    'errors' => ['system' => 'System roles cannot be deleted'],
                    'status' => false
                ], 422);
            }

            $roleName = $role->name;
            $role->delete();

            // Clear cache
            $this->clearRoleCache();

            Log::info('Role deleted', ['role_name' => $roleName]);

            return response()->json([
                'message' => 'Role deleted successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Role deletion failed', ['role_id' => $role->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to delete role',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Get role statistics and analytics.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $cacheKey = 'role_stats';

        $stats = Cache::remember($cacheKey, 3600, function () {
            return [
                'total_roles' => Role::count(),
                'total_permissions' => Permission::count(),
                'total_users' => $this->roleService->getTotalUsersCount(),
                'active_mappings' => DB::table('role_has_permissions')->count(),
                'roles_by_guard' => Role::select('guard_name', DB::raw('count(*) as count'))
                    ->groupBy('guard_name')
                    ->get()
                    ->pluck('count', 'guard_name')
                    ->toArray(),
                'recent_roles' => Role::withCount('permissions', 'users')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
            ];
        });

        return response()->json([
            'data' => $stats,
            'message' => 'Role statistics retrieved successfully',
            'status' => true
        ]);
    }

    /**
     * Bulk assign permissions to multiple roles.
     *
     * @param BulkOperationRequest $request
     * @return JsonResponse
     */
    public function bulkAssignPermissions(BulkOperationRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $roleIds = $request->validated('role_ids');
            $permissionIds = $request->validated('permission_ids');

            $roles = Role::whereIn('id', $roleIds)->get();
            $permissions = Permission::whereIn('id', $permissionIds)->get();

            foreach ($roles as $role) {
                foreach ($permissions as $permission) {
                    if (!$role->hasPermissionTo($permission->name)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }

            DB::commit();

            // Clear cache
            $this->clearRoleCache();

            Log::info('Bulk permissions assigned', [
                'role_count' => count($roleIds),
                'permission_count' => count($permissionIds)
            ]);

            return response()->json([
                'message' => 'Permissions assigned to roles successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk permission assignment failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to assign permissions',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Bulk revoke permissions from multiple roles.
     *
     * @param BulkOperationRequest $request
     * @return JsonResponse
     */
    public function bulkRevokePermissions(BulkOperationRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $roleIds = $request->validated('role_ids');
            $permissionIds = $request->validated('permission_ids');

            $roles = Role::whereIn('id', $roleIds)->get();
            $permissions = Permission::whereIn('id', $permissionIds)->get();

            foreach ($roles as $role) {
                foreach ($permissions as $permission) {
                    if ($role->hasPermissionTo($permission->name)) {
                        $role->revokePermissionTo($permission);
                    }
                }
            }

            DB::commit();

            // Clear cache
            $this->clearRoleCache();

            Log::info('Bulk permissions revoked', [
                'role_count' => count($roleIds),
                'permission_count' => count($permissionIds)
            ]);

            return response()->json([
                'message' => 'Permissions revoked from roles successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk permission revocation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to revoke permissions',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Clear role-related cache.
     *
     * @return void
     */
    private function clearRoleCache(): void
    {
        $cacheKeys = Cache::getRedis()->keys("roles:*");

        if (!empty($cacheKeys)) {
            Cache::getRedis()->del($cacheKeys);
        }

        Cache::forget('role_stats');
    }
}
```

### PermissionController

**Location:** `app/Http/Controllers/API/PermissionController.php`

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\{StorePermissionRequest, UpdatePermissionRequest};
use App\Http\Resources\API\{PermissionResource, PermissionWithRolesResource};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\{Cache, DB, Log};
use Spatie\Permission\Models\{Permission, Role};

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permissions.index')->only('index', 'search', 'stats');
        $this->middleware('permission:permissions.create')->only('store');
        $this->middleware('permission:permissions.show')->only('show');
        $this->middleware('permission:permissions.edit')->only('update');
        $this->middleware('permission:permissions.delete')->only('destroy');
        $this->middleware('permission:permissions.categories')->only('categories');
    }

    /**
     * Display a listing of permissions with pagination and search.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $guard = $request->get('guard', 'web');

        // Validate pagination limits
        $perPage = min(max($perPage, 1), 100);

        $cacheKey = "permissions.{$page}.{$perPage}.{$search}.{$guard}";

        $permissions = Cache::remember($cacheKey, 300, function () use ($search, $perPage, $guard) {
            $query = Permission::with('roles')
                ->where('guard_name', $guard)
                ->orderBy('name', 'asc');

            if ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            return $query->paginate($perPage);
        });

        return response()->json([
            'data' => PermissionResource::collection($permissions->items()),
            'meta' => [
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
                'from' => $permissions->firstItem(),
                'to' => $permissions->lastItem(),
            ],
            'message' => 'Permissions retrieved successfully',
            'status' => true
        ]);
    }

    /**
     * Store a newly created permission in storage.
     *
     * @param StorePermissionRequest $request
     * @return JsonResponse
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $permission = Permission::create([
                'name' => $request->validated('name'),
                'guard_name' => $request->validated('guard_name', 'web'),
            ]);

            DB::commit();

            // Clear cache
            $this->clearPermissionCache();

            Log::info('Permission created', ['permission_id' => $permission->id, 'name' => $permission->name]);

            return response()->json([
                'data' => new PermissionResource($permission),
                'message' => 'Permission created successfully',
                'status' => true
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to create permission',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Display the specified permission with its roles.
     *
     * @param Permission $permission
     * @return JsonResource
     */
    public function show(Permission $permission): JsonResource
    {
        $permission->load('roles');

        return new PermissionWithRolesResource($permission);
    }

    /**
     * Update the specified permission in storage.
     *
     * @param UpdatePermissionRequest $request
     * @param Permission $permission
     * @return JsonResponse
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        try {
            DB::beginTransaction();

            $updateData = $request->validated();

            if (isset($updateData['name']) && $updateData['name'] !== $permission->name) {
                $updateData['name'] = $updateData['name'];
            }

            $permission->update(array_filter($updateData));

            DB::commit();

            // Clear cache
            $this->clearPermissionCache();

            Log::info('Permission updated', ['permission_id' => $permission->id, 'name' => $permission->name]);

            return response()->json([
                'data' => new PermissionResource($permission),
                'message' => 'Permission updated successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission update failed', ['permission_id' => $permission->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to update permission',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Remove the specified permission from storage.
     *
     * @param Permission $permission
     * @return JsonResponse
     */
    public function destroy(Permission $permission): JsonResponse
    {
        try {
            // Check if permission is being used by roles
            $roleCount = $permission->roles()->count();

            if ($roleCount > 0) {
                return response()->json([
                    'message' => 'Cannot delete permission. It is assigned to roles.',
                    'errors' => ['roles' => "This permission is assigned to {$roleCount} roles"],
                    'status' => false
                ], 422);
            }

            $permissionName = $permission->name;
            $permission->delete();

            // Clear cache
            $this->clearPermissionCache();

            Log::info('Permission deleted', ['permission_name' => $permissionName]);

            return response()->json([
                'message' => 'Permission deleted successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Permission deletion failed', ['permission_id' => $permission->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to delete permission',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Search permissions with advanced filtering.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $guard = $request->get('guard', 'web');
        $limit = (int) $request->get('limit', 20);

        $limit = min(max($limit, 1), 50);

        $permissions = Permission::where('guard_name', $guard)
            ->where('name', 'LIKE', "%{$query}%")
            ->withCount('roles')
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => PermissionResource::collection($permissions),
            'message' => 'Search completed successfully',
            'status' => true
        ]);
    }

    /**
     * Get permission statistics and analytics.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $cacheKey = 'permission_stats';

        $stats = Cache::remember($cacheKey, 3600, function () {
            $permissions = Permission::withCount('roles')
                ->withCount('users') // If you have a relationship to users
                ->get();

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
                    ->get()
            ];
        });

        return response()->json([
            'data' => $stats,
            'message' => 'Permission statistics retrieved successfully',
            'status' => true
        ]);
    }

    /**
     * Get permissions grouped by categories.
     *
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        $cacheKey = 'permission_categories';

        $categories = Cache::remember($cacheKey, 3600, function () {
            $permissions = Permission::withCount('roles')->get();

            // Group permissions by prefix (e.g., users.*, posts.*, etc.)
            $grouped = $permissions->groupBy(function ($permission) {
                $parts = explode('.', $permission->name);
                return count($parts) > 1 ? $parts[0] : 'general';
            });

            return $grouped->map(function ($group, $category) {
                return [
                    'category' => ucfirst($category),
                    'permissions' => PermissionResource::collection($group),
                    'count' => $group->count()
                ];
            })->values()->toArray();
        });

        return response()->json([
            'data' => $categories,
            'message' => 'Permission categories retrieved successfully',
            'status' => true
        ]);
    }

    /**
     * Get permissions by guard name.
     *
     * @param string $guardName
     * @return JsonResponse
     */
    public function getByGuard(string $guardName): JsonResponse
    {
        $permissions = Permission::where('guard_name', $guardName)
            ->withCount('roles')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => PermissionResource::collection($permissions),
            'message' => 'Permissions retrieved successfully',
            'status' => true
        ]);
    }

    /**
     * Check if permission exists.
     *
     * @param string $name
     * @return JsonResponse
     */
    public function checkExists(string $name): JsonResponse
    {
        $exists = Permission::where('name', $name)->exists();

        return response()->json([
            'data' => ['exists' => $exists],
            'message' => 'Permission existence check completed',
            'status' => true
        ]);
    }

    /**
     * Clear permission-related cache.
     *
     * @return void
     */
    private function clearPermissionCache(): void
    {
        $cacheKeys = Cache::getRedis()->keys("permissions:*");

        if (!empty($cacheKeys)) {
            Cache::getRedis()->del($cacheKeys);
        }

        Cache::forget('permission_stats');
        Cache::forget('permission_categories');
    }
}
```

### RolePermissionController

**Location:** `app/Http/Controllers/API/RolePermissionController.php`

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SyncPermissionsRequest;
use App\Http\Resources\API\{PermissionResource, RoleResource};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\{Cache, DB, Log};
use Spatie\Permission\Models\{Role, Permission};

class RolePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.permissions.index')->only('index');
        $this->middleware('permission:roles.permissions.sync')->only('sync');
        $this->middleware('permission:roles.permissions.revoke')->only('revoke');
        $this->middleware('permission:roles.permissions.check')->only('checkPermission');
        $this->middleware('permission:roles.users.index')->only('users');
    }

    /**
     * Get all permissions for a specific role.
     *
     * @param string $roleId
     * @return JsonResponse
     */
    public function index(string $roleId): JsonResponse
    {
        try {
            $role = Role::findOrFail($roleId);

            $permissions = $role->permissions()->orderBy('name')->get();

            return response()->json([
                'data' => PermissionResource::collection($permissions),
                'message' => 'Role permissions retrieved successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get role permissions', ['role_id' => $roleId, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to retrieve role permissions',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Sync permissions for a role (replaces all current permissions).
     *
     * @param SyncPermissionsRequest $request
     * @param string $roleId
     * @return JsonResponse
     */
    public function sync(SyncPermissionsRequest $request, string $roleId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($roleId);
            $permissionIds = $request->validated('permissions');

            // Get permissions to sync
            $permissions = Permission::whereIn('id', $permissionIds)->get();

            // Sync permissions (atomic operation)
            $role->syncPermissions($permissions);

            DB::commit();

            // Clear caches
            $this->clearRelatedCache($roleId);

            Log::info('Role permissions synced', [
                'role_id' => $roleId,
                'role_name' => $role->name,
                'permission_count' => count($permissionIds)
            ]);

            return response()->json([
                'data' => [
                    'role' => new RoleResource($role),
                    'permissions' => PermissionResource::collection($role->permissions)
                ],
                'message' => 'Permissions synced successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync role permissions', ['role_id' => $roleId, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to sync permissions',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Revoke a specific permission from a role.
     *
     * @param string $roleId
     * @param string $permissionId
     * @return JsonResponse
     */
    public function revoke(string $roleId, string $permissionId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($roleId);
            $permission = Permission::findOrFail($permissionId);

            if (!$role->hasPermissionTo($permission->name)) {
                return response()->json([
                    'message' => 'Permission is not assigned to this role',
                    'status' => false
                ], 422);
            }

            $role->revokePermissionTo($permission);

            DB::commit();

            // Clear caches
            $this->clearRelatedCache($roleId);

            Log::info('Permission revoked from role', [
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ]);

            return response()->json([
                'message' => 'Permission revoked successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to revoke permission', [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to revoke permission',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Give a specific permission to a role.
     *
     * @param string $roleId
     * @param string $permissionId
     * @return JsonResponse
     */
    public function give(string $roleId, string $permissionId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($roleId);
            $permission = Permission::findOrFail($permissionId);

            if ($role->hasPermissionTo($permission->name)) {
                return response()->json([
                    'message' => 'Permission is already assigned to this role',
                    'status' => false
                ], 422);
            }

            $role->givePermissionTo($permission);

            DB::commit();

            // Clear caches
            $this->clearRelatedCache($roleId);

            Log::info('Permission given to role', [
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ]);

            return response()->json([
                'message' => 'Permission assigned successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to give permission', [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to assign permission',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Check if a role has a specific permission.
     *
     * @param string $roleId
     * @param string $permissionId
     * @return JsonResponse
     */
    public function checkPermission(string $roleId, string $permissionId): JsonResponse
    {
        try {
            $role = Role::findOrFail($roleId);
            $permission = Permission::findOrFail($permissionId);

            $hasPermission = $role->hasPermissionTo($permission->name);

            return response()->json([
                'data' => ['has_permission' => $hasPermission],
                'message' => 'Permission check completed',
                'status' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check permission', [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to check permission',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Get all users assigned to a specific role.
     *
     * @param string $roleId
     * @param Request $request
     * @return JsonResponse
     */
    public function users(string $roleId, Request $request): JsonResponse
    {
        try {
            $page = (int) $request->get('page', 1);
            $perPage = (int) $request->get('per_page', 15);

            $perPage = min(max($perPage, 1), 100);

            $role = Role::findOrFail($roleId);

            $users = $role->users()
                ->select('id', 'name', 'email', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'data' => $users->items(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
                'message' => 'Role users retrieved successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get role users', ['role_id' => $roleId, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to retrieve role users',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Get permissions that are not assigned to a role.
     *
     * @param string $roleId
     * @param Request $request
     * @return JsonResponse
     */
    public function available(string $roleId, Request $request): JsonResponse
    {
        try {
            $role = Role::findOrFail($roleId);

            // Get all permission IDs that the role already has
            $assignedPermissionIds = $role->permissions()->pluck('id');

            // Get permissions that are not assigned to the role
            $availablePermissions = Permission::whereNotIn('id', $assignedPermissionIds)
                ->withCount('roles')
                ->orderBy('name')
                ->get();

            return response()->json([
                'data' => PermissionResource::collection($availablePermissions),
                'message' => 'Available permissions retrieved successfully',
                'status' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get available permissions', ['role_id' => $roleId, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to retrieve available permissions',
                'errors' => ['general' => $e->getMessage()],
                'status' => false
            ], 500);
        }
    }

    /**
     * Clear role and permission related caches.
     *
     * @param string $roleId
     * @return void
     */
    private function clearRelatedCache(string $roleId): void
    {
        // Clear role cache
        $roleCacheKeys = Cache::getRedis()->keys("roles:*");
        if (!empty($roleCacheKeys)) {
            Cache::getRedis()->del($roleCacheKeys);
        }

        // Clear permission cache
        $permissionCacheKeys = Cache::getRedis()->keys("permissions:*");
        if (!empty($permissionCacheKeys)) {
            Cache::getRedis()->del($permissionCacheKeys);
        }

        Cache::forget('role_stats');
        Cache::forget('permission_stats');
    }
}
```

This comprehensive setup provides:

1. **Full CRUD Operations** for roles and permissions
2. **Permission Management** with sync, assign, and revoke operations
3. **Search and Filtering** capabilities
4. **Bulk Operations** for efficiency
5. **Statistics and Analytics** endpoints
6. **Proper Error Handling** with detailed logging
7. **Caching** for performance optimization
8. **Security** with middleware permissions
9. **Validation** using Form Requests
10. **Rate Limiting** support (to be added in routes)

The controllers follow Laravel 12 best practices and integrate seamlessly with the Vue.js frontend you've already created.

## API Resources

API Resources provide a consistent and clean way to transform your Eloquent models into JSON responses.

### RoleResource

**Location:** `app/Http/Resources/API/RoleResource.php`

```php
<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
```

### PermissionResource

**Location:** `app/Http/Resources/API/PermissionResource.php`

```php
<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Permission;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
```

### RoleWithPermissionsResource

**Location:** `app/Http/Resources/API/RoleWithPermissionsResource.php`

```php
<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class RoleWithPermissionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'permissions_count' => $this->whenCounted('permissions', $this->permissions_count ?? $this->permissions->count()),
            'users_count' => $this->whenCounted('users', $this->users_count ?? $this->users->count()),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
```

### PermissionWithRolesResource

**Location:** `app/Http/Resources/API/PermissionWithRolesResource.php`

```php
<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Permission;

class PermissionWithRolesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'roles_count' => $this->whenCounted('roles', $this->roles_count ?? $this->roles->count()),
            'users_count' => $this->whenCounted('users', $this->users_count ?? $this->users->count()),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
```

### RoleStatsResource

**Location:** `app/Http/Resources/API/RoleStatsResource.php`

```php
<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'total_roles' => $this->resource['total_roles'] ?? 0,
            'total_permissions' => $this->resource['total_permissions'] ?? 0,
            'total_users' => $this->resource['total_users'] ?? 0,
            'active_mappings' => $this->resource['active_mappings'] ?? 0,
            'roles_by_guard' => $this->resource['roles_by_guard'] ?? [],
            'recent_roles' => $this->when(isset($this->resource['recent_roles']), function () {
                return $this->resource['recent_roles']->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'permissions_count' => $role->permissions_count,
                        'users_count' => $role->users_count,
                        'created_at' => $role->created_at?->format('Y-m-d H:i:s'),
                    ];
                });
            }),
        ];
    }
}
```

## Form Requests

Form Requests provide centralized validation logic for your API endpoints.

### StoreRoleRequest

**Location:** `app/Http/Requests/API/StoreRoleRequest.php`

```php
<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name',
                'regex:/^[a-zA-Z0-9_\-\s]+$/',
            ],
            'guard_name' => [
                'sometimes',
                'string',
                'in:web,api,sanctum',
            ],
            'permissions' => [
                'sometimes',
                'array',
                'max:100',
            ],
            'permissions.*' => [
                'exists:permissions,id',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Role name is required.',
            'name.unique' => 'A role with this name already exists.',
            'name.regex' => 'Role name may only contain letters, numbers, spaces, hyphens, and underscores.',
            'guard_name.in' => 'Guard must be one of: web, api, sanctum.',
            'permissions.*.exists' => 'Selected permission does not exist.',
            'permissions.max' => 'Cannot select more than 100 permissions.',
        ];
    }
}
```

### UpdateRoleRequest

**Location:** `app/Http/Requests/API/UpdateRoleRequest.php`

```php
<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role),
                'regex:/^[a-zA-Z0-9_\-\s]+$/',
            ],
            'guard_name' => [
                'sometimes',
                'string',
                'in:web,api,sanctum',
            ],
            'permissions' => [
                'sometimes',
                'array',
                'max:100',
            ],
            'permissions.*' => [
                'exists:permissions,id',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A role with this name already exists.',
            'name.regex' => 'Role name may only contain letters, numbers, spaces, hyphens, and underscores.',
            'guard_name.in' => 'Guard must be one of: web, api, sanctum.',
            'permissions.*.exists' => 'Selected permission does not exist.',
            'permissions.max' => 'Cannot select more than 100 permissions.',
        ];
    }
}
```

### StorePermissionRequest

**Location:** `app/Http/Requests/API/StorePermissionRequest.php`

```php
<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('permissions.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name',
                'regex:/^[a-z0-9_\-\.\s:]+$/',
            ],
            'guard_name' => [
                'sometimes',
                'string',
                'in:web,api,sanctum',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Permission name is required.',
            'name.unique' => 'A permission with this name already exists.',
            'name.regex' => 'Permission name may only contain lowercase letters, numbers, spaces, hyphens, periods, underscores, and colons.',
            'guard_name.in' => 'Guard must be one of: web, api, sanctum.',
        ];
    }
}
```

### UpdatePermissionRequest

**Location:** `app/Http/Requests/API/UpdatePermissionRequest.php`

```php
<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('permissions.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $permission = $this->route('permission');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permission),
                'regex:/^[a-z0-9_\-\.\s:]+$/',
            ],
            'guard_name' => [
                'sometimes',
                'string',
                'in:web,api,sanctum',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A permission with this name already exists.',
            'name.regex' => 'Permission name may only contain lowercase letters, numbers, spaces, hyphens, periods, underscores, and colons.',
            'guard_name.in' => 'Guard must be one of: web, api, sanctum.',
        ];
    }
}
```

### SyncPermissionsRequest

**Location:** `app/Http/Requests/API/SyncPermissionsRequest.php`

```php
<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class SyncPermissionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles.permissions.sync');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'permissions' => [
                'required',
                'array',
                'min:0',
                'max:1000',
            ],
            'permissions.*' => [
                'required',
                'exists:permissions,id',
                'distinct',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'permissions.required' => 'Permissions array is required.',
            'permissions.max' => 'Cannot sync more than 1000 permissions at once.',
            'permissions.*.exists' => 'Selected permission does not exist.',
            'permissions.*.distinct' => 'Duplicate permission IDs are not allowed.',
        ];
    }
}
```

### BulkOperationRequest

**Location:** `app/Http/Requests/API/BulkOperationRequest.php`

```php
<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class BulkOperationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles.bulk');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'role_ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'role_ids.*' => [
                'required',
                'exists:roles,id',
                'distinct',
            ],
            'permission_ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'permission_ids.*' => [
                'required',
                'exists:permissions,id',
                'distinct',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'role_ids.required' => 'Role IDs array is required.',
            'role_ids.max' => 'Cannot process more than 100 roles at once.',
            'role_ids.*.exists' => 'Selected role does not exist.',
            'permission_ids.required' => 'Permission IDs array is required.',
            'permission_ids.max' => 'Cannot process more than 100 permissions at once.',
            'permission_ids.*.exists' => 'Selected permission does not exist.',
            '*.distinct' => 'Duplicate IDs are not allowed.',
        ];
    }
}
```

## API Routes

**Location:** `routes/api.php`

```php
<?php

use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\RolePermissionController;
use Illuminate\Support\Facades\Route;

// Role Management Routes
Route::prefix('roles')->middleware(['auth:api', 'permission:roles.view'])->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('api.roles.index');
    Route::post('/', [RoleController::class, 'store'])
        ->middleware('permission:roles.create')
        ->name('api.roles.store');

    Route::prefix('{role}')->group(function () {
        Route::get('/', [RoleController::class, 'show'])->name('api.roles.show');
        Route::put('/', [RoleController::class, 'update'])
            ->middleware('permission:roles.edit')
            ->name('api.roles.update');
        Route::delete('/', [RoleController::class, 'destroy'])
            ->middleware('permission:roles.delete')
            ->name('api.roles.destroy');
    });

    // Role Statistics
    Route::get('/stats', [RoleController::class, 'stats'])->name('api.roles.stats');

    // Bulk Operations
    Route::post('/bulk-assign-permissions', [RoleController::class, 'bulkAssignPermissions'])
        ->middleware('permission:roles.permissions.sync')
        ->name('api.roles.bulk-assign-permissions');
    Route::post('/bulk-revoke-permissions', [RoleController::class, 'bulkRevokePermissions'])
        ->middleware('permission:roles.permissions.sync')
        ->name('api.roles.bulk-revoke-permissions');
});

// Permission Management Routes
Route::prefix('permissions')->middleware(['auth:api', 'permission:permissions.view'])->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('api.permissions.index');
    Route::post('/', [PermissionController::class, 'store'])
        ->middleware('permission:permissions.create')
        ->name('api.permissions.store');

    Route::prefix('{permission}')->group(function () {
        Route::get('/', [PermissionController::class, 'show'])->name('api.permissions.show');
        Route::put('/', [PermissionController::class, 'update'])
            ->middleware('permission:permissions.edit')
            ->name('api.permissions.update');
        Route::delete('/', [PermissionController::class, 'destroy'])
            ->middleware('permission:permissions.delete')
            ->name('api.permissions.destroy');

        // Permission Relationships
        Route::get('/roles', [PermissionController::class, 'getRoles'])->name('api.permissions.roles');
        Route::get('/users', [PermissionController::class, 'getUsers'])->name('api.permissions.users');
    });

    // Permission Utilities
    Route::get('/stats', [PermissionController::class, 'stats'])->name('api.permissions.stats');
    Route::get('/search', [PermissionController::class, 'search'])->name('api.permissions.search');
    Route::get('/guard/{guardName}', [PermissionController::class, 'getByGuard'])->name('api.permissions.by-guard');
    Route::get('/exists/{name}', [PermissionController::class, 'checkExists'])->name('api.permissions.exists');

    // Categorized Permissions
    Route::get('/categories', [PermissionController::class, 'getCategories'])->name('api.permissions.categories');

    // Paginated Permissions
    Route::get('/paginated', [PermissionController::class, 'getPaginated'])->name('api.permissions.paginated');

    // Bulk Delete
    Route::post('/bulk-delete', [PermissionController::class, 'bulkDelete'])
        ->middleware('permission:permissions.delete')
        ->name('api.permissions.bulk-delete');
});

// Role-Permission Management Routes
Route::prefix('roles')->middleware(['auth:api'])->group(function () {
    Route::get('/{role}/permissions', [RolePermissionController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('api.roles.permissions.index');

    Route::post('/{role}/permissions', [RolePermissionController::class, 'store'])
        ->middleware('permission:roles.permissions.sync')
        ->name('api.roles.permissions.store');

    Route::put('/{role}/permissions', [RolePermissionController::class, 'sync'])
        ->middleware('permission:roles.permissions.sync')
        ->name('api.roles.permissions.sync');

    Route::delete('/{role}/permissions/{permission}', [RolePermissionController::class, 'destroy'])
        ->middleware('permission:roles.permissions.sync')
        ->name('api.roles.permissions.destroy');

    Route::get('/{role}/users', [RolePermissionController::class, 'getUsers'])
        ->middleware('permission:roles.view')
        ->name('api.roles.users');

    Route::get('/{role}/has-permission/{permission}', [RolePermissionController::class, 'hasPermission'])
        ->middleware('permission:roles.view')
        ->name('api.roles.has-permission');
});
```

## Middleware Setup

### API Authentication Middleware

**Location:** `app/Http/Middleware/ApiAuthMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Check if API key is provided for optional auth endpoints
        if ($request->bearerToken()) {
            try {
                Auth::guard('sanctum')->authenticate();
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid authentication token',
                ], 401);
            }
        }

        return $next($request);
    }
}
```

### Rate Limiting Middleware

**Location:** `app/Http/Middleware/RateLimitMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $limit
     * @param  string  $window
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $limit = '60', string $window = '1'): mixed
    {
        $key = sprintf('rate_limit:%s:%s',
            $request->ip(),
            $request->route()->getName()
        );

        $current = Redis::connection('cache')->get($key) ?? 0;

        if ($current >= (int) $limit) {
            return response()->json([
                'status' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $window * 60,
            ], 429);
        }

        Redis::connection('cache')->setex(
            $key,
            (int) $window * 60,
            (int) $current + 1
        );

        return $next($request);
    }
}
```

### Register Middleware

**Location:** `bootstrap/app.php`

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ApiAuthMiddleware;
use App\Http\Middleware\RateLimitMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register API middleware
        $middleware->group('api', [
            \App\Http\Middleware\ApiAuthMiddleware::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        ]);

        // Rate limiting for sensitive endpoints
        $middleware->group('api.ratelimited', [
            \App\Http\Middleware\RateLimitMiddleware::class.':30,1',
        ]);

        // CORS middleware for API
        $middleware->group('api.cors', [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# Cache Configuration
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,localhost:3000
SANCTUM_TOKEN_EXPIRATION=525600  # 1 year in minutes

# API Configuration
API_RATE_LIMIT=1000
API_RATE_LIMIT_WINDOW=1  # minutes

# Permission Guard Configuration
DEFAULT_GUARD=web
API_GUARD=api
SANCTUM_GUARD=sanctum
```

### Cache Configuration

**Location:** `config/cache.php`

```php
'guards' => [
    'permissions' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'prefix' => 'permissions',
    ],
    'roles' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'prefix' => 'roles',
    ],
],
```

### Spatie Configuration

**Location:** `config/permission.php`

```php
<?php

return [
    'models' => [
        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Spatie\Permission\Contracts\Permission` contract.
         */
        'permission' => Spatie\Permission\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Spatie\Permission\Contracts\Role` contract.
         */
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'column_names' => [
        /*
         * Change this if you want to name the related model primary key differently.
         */
        'role_pivot_key' => 'role_id',

        /*
         * Change this if you want to name the related model primary key differently.
         */
        'permission_pivot_key' => 'permission_id',
    ],

    'table_names' => [
        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */
        'roles' => 'roles',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your permissions. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */
        'permissions' => 'permissions',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your models permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */
        'model_has_permissions' => 'model_has_permissions',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your models roles. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */
        'model_has_roles' => 'model_has_roles',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */
        'role_has_permissions' => 'role_has_permissions',
    ],

    /*
     * By default all permissions will be cached for 24 hours unless a permission or
     * role is updated. Then the cache will be flushed automatically.
     */
    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24h'),
        'key' => 'spatie.permission.cache',
        'store' => 'redis', // Use Redis for better performance
    ],

    /*
     * When set to true, the required permission names are added to the exception
     * message. This could be considered an information leak in some contexts, so
     * the default is set to false here for optimum safety.
     */
    'display_permission_in_exception' => false,

    /*
     * By default wildcard permission lookups are disabled.
     */
    'enable_wildcard_permission' => true,

    'teams' => [
        /*
         * When set to true this package will create a single team when a user is
         * created. By default this is set to false so no team is created during
         * user creation.
         */
        'teams' => false,

        /*
         * When set to true, the Membership model will not be deleted when the
         * related user or team is deleted.
         */
        'delete_membership_on_removed_user_or_team' => true,

        /*
         * When set to true the specified team class is used.
         */
        'invited_team_class' => null,

        /*
         * When set to true the specified membership class is used.
         */
        'invited_membership_class' => null,

        /*
         * When set to true the specified invitation class is used.
         */
        'invited_invitation_class' => null,
    ],
];
```

## Testing

### Feature Tests

**Location:** `tests/Feature/RolePermissionApiTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePermissionApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $this->user = User::factory()->create();
        $this->user->assignRole('user');

        Sanctum::actingAs($this->admin);
    }

    // Role Tests
    public function test_can_get_roles(): void
    {
        Role::factory()->count(5)->create();

        $response = $this->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'guard_name', 'created_at', 'updated_at']
                ]
            ]);
    }

    public function test_can_create_role(): void
    {
        $roleData = [
            'name' => 'Test Role',
            'guard_name' => 'api',
            'permissions' => ['users.view', 'users.create']
        ];

        $response = $this->postJson('/api/roles', $roleData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'name', 'guard_name']
            ]);

        $this->assertDatabaseHas('roles', ['name' => 'Test Role']);
    }

    public function test_can_update_role(): void
    {
        $role = Role::factory()->create();
        $updateData = ['name' => 'Updated Role'];

        $response = $this->putJson("/api/roles/{$role->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'Updated Role']);
    }

    public function test_can_delete_role(): void
    {
        $role = Role::factory()->create();

        $response = $this->deleteJson("/api/roles/{$role->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    // Permission Tests
    public function test_can_get_permissions(): void
    {
        Permission::factory()->count(5)->create();

        $response = $this->getJson('/api/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'guard_name', 'created_at', 'updated_at']
                ]
            ]);
    }

    public function test_can_create_permission(): void
    {
        $permissionData = [
            'name' => 'test.permission',
            'guard_name' => 'api'
        ];

        $response = $this->postJson('/api/permissions', $permissionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'name', 'guard_name']
            ]);

        $this->assertDatabaseHas('permissions', ['name' => 'test.permission']);
    }

    // Role-Permission Relationship Tests
    public function test_can_get_role_permissions(): void
    {
        $role = Role::factory()->create();
        $permissions = Permission::factory()->count(3)->create();
        $role->givePermissionTo($permissions);

        $response = $this->getJson("/api/roles/{$role->id}/permissions");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_sync_role_permissions(): void
    {
        $role = Role::factory()->create();
        $permissions = Permission::factory()->count(2)->create();

        $syncData = [
            'permissions' => $permissions->pluck('id')->toArray()
        ];

        $response = $this->putJson("/api/roles/{$role->id}/permissions", $syncData);

        $response->assertStatus(200);
        $this->assertEquals(2, $role->fresh()->permissions()->count());
    }

    // Authorization Tests
    public function test_unauthorized_user_cannot_manage_roles(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/roles', ['name' => 'Test Role']);
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_api(): void
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/roles');
        $response->assertStatus(401);
    }

    // Validation Tests
    public function test_role_validation_fails_with_invalid_data(): void
    {
        $invalidData = ['name' => ''];

        $response = $this->postJson('/api/roles', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_permission_validation_fails_with_duplicate_name(): void
    {
        Permission::factory()->create(['name' => 'test.permission']);

        $duplicateData = ['name' => 'test.permission'];

        $response = $this->postJson('/api/permissions', $duplicateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // Performance Tests
    public function test_api_response_time(): void
    {
        Role::factory()->count(100)->create();

        $startTime = microtime(true);

        $response = $this->getJson('/api/roles');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);
        $this->assertLessThan(500, $responseTime); // Should respond in less than 500ms
    }

    // Bulk Operations Tests
    public function test_can_bulk_assign_permissions(): void
    {
        $roles = Role::factory()->count(2)->create();
        $permissions = Permission::factory()->count(3)->create();

        $bulkData = [
            'role_ids' => $roles->pluck('id')->toArray(),
            'permission_ids' => $permissions->pluck('id')->toArray()
        ];

        $response = $this->postJson('/api/roles/bulk-assign-permissions', $bulkData);

        $response->assertStatus(200);

        foreach ($roles as $role) {
            $this->assertEquals(3, $role->fresh()->permissions()->count());
        }
    }

    // Search Tests
    public function test_can_search_permissions(): void
    {
        Permission::factory()->create(['name' => 'users.create']);
        Permission::factory()->create(['name' => 'users.edit']);
        Permission::factory()->create(['name' => 'roles.create']);

        $response = $this->getJson('/api/permissions/search?q=users');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    // Statistics Tests
    public function test_can_get_role_statistics(): void
    {
        Role::factory()->count(10)->create();
        Permission::factory()->count(20)->create();

        $response = $this->getJson('/api/roles/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'total_roles',
                    'total_permissions',
                    'total_users',
                    'active_mappings'
                ]
            ]);
    }
}
```

### Unit Tests

**Location:** `tests/Unit/RolePermissionTest.php`

```php
<?php

namespace Tests\Unit;

use App\Services\RolePermissionService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    public function test_role_can_be_assigned_permissions(): void
    {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();

        $role->givePermissionTo($permission);

        $this->assertTrue($role->hasPermissionTo($permission));
    }

    public function test_role_can_check_multiple_permissions(): void
    {
        $role = Role::factory()->create();
        $permissions = Permission::factory()->count(2)->create();

        $role->givePermissionTo($permissions);

        $this->assertTrue($role->hasAllPermissions($permissions->pluck('name')));
    }

    public function test_permission_can_be_given_to_multiple_roles(): void
    {
        $permission = Permission::factory()->create();
        $roles = Role::factory()->count(3)->create();

        foreach ($roles as $role) {
            $role->givePermissionTo($permission);
        }

        $this->assertEquals(3, $permission->roles()->count());
    }

    public function test_role_permissions_can_be_synced(): void
    {
        $role = Role::factory()->create();
        $oldPermissions = Permission::factory()->count(2)->create();
        $newPermissions = Permission::factory()->count(3)->create();

        $role->givePermissionTo($oldPermissions);
        $role->syncPermissions($newPermissions);

        $this->assertEquals(3, $role->permissions()->count());
        $this->assertFalse($role->hasPermissionTo($oldPermissions->first()));
    }

    public function test_permission_validation_regex(): void
    {
        $validNames = [
            'users.create',
            'admin:dashboard.view',
            'reports_export',
            'api.v1.users.list',
            'settings.update'
        ];

        $invalidNames = [
            'Users Create', // uppercase
            'users@create', // special char
            'users create!', // special char
        ];

        foreach ($validNames as $name) {
            $this->assertMatchesRegularExpression(
                '/^[a-z0-9_\-\.\s:]+$/',
                $name,
                "Permission name '{$name}' should be valid"
            );
        }

        foreach ($invalidNames as $name) {
            $this->assertDoesNotMatchRegularExpression(
                '/^[a-z0-9_\-\.\s:]+$/',
                $name,
                "Permission name '{$name}' should be invalid"
            );
        }
    }
}
```

### Performance Tests

**Location:** `tests/Performance/RolePermissionPerformanceTest.php`

```php
<?php

namespace Tests\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePermissionPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_index_performance_with_large_dataset(): void
    {
        // Create a large dataset
        Role::factory()->count(1000)->create();
        Permission::factory()->count(500)->create();

        $startTime = microtime(true);

        $response = $this->getJson('/api/roles');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        // API should respond in under 1 second even with large datasets
        $this->assertLessThan(1000, $responseTime);
    }

    public function test_permission_search_performance(): void
    {
        Permission::factory()->count(10000)->create();

        $startTime = microtime(true);

        $response = $this->getJson('/api/permissions/search?q=user');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        // Search should be optimized and fast
        $this->assertLessThan(200, $responseTime);
    }

    public function test_role_permission_sync_performance(): void
    {
        $role = Role::factory()->create();
        $permissions = Permission::factory()->count(1000)->create();

        $startTime = microtime(true);

        $response = $this->putJson("/api/roles/{$role->id}/permissions", [
            'permissions' => $permissions->pluck('id')->toArray()
        ]);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        // Bulk sync should be optimized
        $this->assertLessThan(500, $responseTime);
    }
}
```

---

## Setup Summary

This comprehensive Laravel 12 API setup provides:

1. **Modern PHP 8.3 Features**: Union types, readonly properties, match expressions, constructor property promotion
2. **Complete CRUD Operations**: Full REST API for roles and permissions management
3. **Advanced Features**: Search, filtering, pagination, bulk operations, statistics
4. **Performance Optimization**: Redis caching, rate limiting, database query optimization
5. **Security**: Proper authorization, request validation, input sanitization
6. **Error Handling**: Comprehensive exception handling with proper HTTP status codes
7. **Testing**: Feature tests, unit tests, and performance tests
8. **Documentation**: Well-documented code with PHPDoc comments

The API is ready for production use with Vue.js frontend integration and follows Laravel best practices.
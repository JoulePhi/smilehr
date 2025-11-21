<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SyncPermissionsRequest;
use App\Http\Resources\API\{PermissionResource, RoleResource};
use App\Services\RoleService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\{Cache, DB, Log};
use Spatie\Permission\Models\{Role, Permission};

class RolePermissionController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {}

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
                'success' => true,
                'message' => 'Role permissions retrieved successfully.',
                'data' => PermissionResource::collection($permissions),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get role permissions', ['role_id' => $roleId, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role permissions.',
                'data' => null
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
                'success' => true,
                'message' => 'Permissions synced successfully.',
                'data' => [
                    'role' => new RoleResource($role),
                    'permissions' => PermissionResource::collection($role->permissions)
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync role permissions', ['role_id' => $roleId, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync permissions.',
                'data' => null
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
                    'success' => false,
                    'message' => 'Permission is not assigned to this role.',
                    'data' => null
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
                'success' => true,
                'message' => 'Permission revoked successfully.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to revoke permission', [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke permission.',
                'data' => null
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
                    'success' => false,
                    'message' => 'Permission is already assigned to this role.',
                    'data' => null
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
                'success' => true,
                'message' => 'Permission assigned successfully.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to give permission', [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permission.',
                'data' => null
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
                'success' => true,
                'message' => 'Permission check completed.',
                'data' => [
                    'has_permission' => $hasPermission
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check permission', [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check permission.',
                'data' => null
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

            $users = $this->roleService->getRoleUsers($role, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Role users retrieved successfully.',
                'data' =>  $users->items(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get role users', ['role_id' => $roleId, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role users.',
                'data' => null
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

            $availablePermissions = $this->roleService->getAvailablePermissionsForRole($role);

            return response()->json([
                'success' => true,
                'message' => 'Available permissions retrieved successfully.',
                'data' => [
                    'permissions' => PermissionResource::collection($availablePermissions)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get available permissions', ['role_id' => $roleId, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available permissions.',
                'data' => null
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
        // $roleCacheKeys = Cache::getRedis()->keys("roles:*");
        // if (!empty($roleCacheKeys)) {
        //     Cache::getRedis()->del($roleCacheKeys);
        // }

        // // Clear permission cache
        // $permissionCacheKeys = Cache::getRedis()->keys("permissions:*");
        // if (!empty($permissionCacheKeys)) {
        //     Cache::getRedis()->del($permissionCacheKeys);
        // }

        Cache::forget('role_stats');
        Cache::forget('permission_stats');
    }
}

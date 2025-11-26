<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\{StoreRoleRequest, UpdateRoleRequest, BulkOperationRequest};
use App\Http\Resources\Api\MasterData\{RoleResource, RoleWithPermissionsResource, RoleStatsResource};
use App\Services\MasterData\RoleService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\{Cache, DB, Log};
use Spatie\Permission\Models\{Role, Permission};

class RoleController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {}

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

        $perPage = min(max($perPage, 1), 100);


        $query = Role::with('permissions')
            ->where('guard_name', $guard)
            ->orderBy('id', 'asc');

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $roles =  $query->paginate($perPage);
        return response()->json([
            'success' => true,
            'message' => 'Roles retrieved successfully.',
            'data' => RoleResource::collection($roles->items()),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'from' => $roles->firstItem(),
                'to' => $roles->lastItem(),
            ]
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
                'success' => true,
                'message' => 'Role created successfully.',
                'data' => new RoleWithPermissionsResource($role->load('permissions'))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create role.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Display the specified role with its permissions.
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role retrieved successfully.',
            'data' => new RoleWithPermissionsResource($role)
        ], 200);
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
                'success' => true,
                'message' => 'Role updated successfully.',
                'data' => new RoleWithPermissionsResource($role->load('permissions'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role update failed', ['role_id' => $role->id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update role.',
                'data' => null
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
            // Check if role can be deleted
            $canDelete = $this->roleService->canDeleteRole($role);

            if (!$canDelete['can_delete']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role.',
                    'data' => [
                        'reasons' => $canDelete['reasons'],
                        'user_count' => $canDelete['user_count'],
                        'is_system_role' => $canDelete['is_system_role']
                    ]
                ], 422);
            }

            $roleName = $role->name;
            $role->delete();

            // Clear cache
            $this->clearRoleCache();

            Log::info('Role deleted', ['role_name' => $roleName]);

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('Role deletion failed', ['role_id' => $role->id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role.',
                'data' => null
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
            return $this->roleService->getRoleStatistics();
        });

        return response()->json([
            'success' => true,
            'message' => 'Role statistics retrieved successfully.',
            'data' => new RoleStatsResource($stats)
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

            $result = $this->roleService->bulkAssignPermissions($roleIds, $permissionIds);

            DB::commit();

            // Clear cache
            $this->clearRoleCache();

            Log::info('Bulk permissions assigned', $result);

            return response()->json([
                'success' => true,
                'message' => 'Permissions assigned to roles successfully.',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk permission assignment failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions.',
                'data' => null
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

            $result = $this->roleService->bulkRevokePermissions($roleIds, $permissionIds);

            DB::commit();

            // Clear cache
            $this->clearRoleCache();

            Log::info('Bulk permissions revoked', $result);

            return response()->json([
                'success' => true,
                'message' => 'Permissions revoked from roles successfully.',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk permission revocation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke permissions.',
                'data' => null
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
        // $cacheKeys = Cache::getRedis()->keys("roles:*");

        // if (!empty($cacheKeys)) {
        //     Cache::getRedis()->del($cacheKeys);
        // }

        Cache::forget('role_stats');
    }
}

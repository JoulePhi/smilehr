<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\{StorePermissionRequest, UpdatePermissionRequest};
use App\Http\Resources\Api\MasterData\{PermissionResource, PermissionWithRolesResource};
use App\Services\MasterData\RoleService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\{Cache, DB, Log};
use Spatie\Permission\Models\{Permission, Role};

class PermissionController extends Controller
{
    public function __construct(
        private RoleService $roleService
    ) {}

    /**
     * Display a listing of permissions with pagination and search.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $guard = $request->get('guard', 'web');


        $cacheKey = "permissions.{$search}.{$guard}";

        $query = Permission::with('roles')
            ->where('guard_name', $guard)
            ->orderBy('updated_at', 'desc');

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $permissions = $query->get();
        return response()->json([
            'success' => true,
            'message' => 'Permissions retrieved successfully.',
            'data' => PermissionResource::collection($permissions->all()),
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
                'success' => true,
                'message' => 'Permission created successfully.',
                'data' => new PermissionResource($permission)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create permission.',
                'data' => null
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
                'success' => true,
                'message' => 'Permission updated successfully.',
                'data' => new PermissionResource($permission)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission update failed', ['permission_id' => $permission->id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission.',
                'data' => null
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
            // Check if permission can be deleted
            $canDelete = $this->roleService->canDeletePermission($permission);

            if (!$canDelete['can_delete']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete permission.',
                    'data' => [
                        'reasons' => $canDelete['reasons'],
                        'role_count' => $canDelete['role_count']
                    ]
                ], 422);
            }

            $permissionName = $permission->name;
            $permission->delete();

            // Clear cache
            $this->clearPermissionCache();

            Log::info('Permission deleted', ['permission_name' => $permissionName]);

            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            Log::error('Permission deletion failed', ['permission_id' => $permission->id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permission.',
                'data' => null
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

        $permissions = $this->roleService->searchPermissions($query, $guard, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Search completed successfully.',
            'data' => [
                'permissions' => PermissionResource::collection($permissions)
            ]
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
            return $this->roleService->getPermissionStatistics();
        });

        return response()->json([
            'success' => true,
            'message' => 'Permission statistics retrieved successfully.',
            'data' => $stats
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
            return $this->roleService->getPermissionsByCategories();
        });

        return response()->json([
            'success' => true,
            'message' => 'Permission categories retrieved successfully.',
            'data' => $categories
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
            'success' => true,
            'message' => 'Permissions retrieved successfully.',
            'data' => [
                'permissions' => PermissionResource::collection($permissions)
            ]
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
            'success' => true,
            'message' => 'Permission existence check completed.',
            'data' => [
                'exists' => $exists
            ]
        ]);
    }

    /**
     * Clear permission-related cache.
     *
     * @return void
     */
    private function clearPermissionCache(): void
    {
        // $cacheKeys = Cache::getRedis()->keys("permissions:*");

        // if (!empty($cacheKeys)) {
        //     Cache::getRedis()->del($cacheKeys);
        // }

        Cache::forget('permission_stats');
        Cache::forget('permission_categories');
    }
}

<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MasterData\StoreDepartmentRequest;
use App\Http\Requests\Api\MasterData\UpdateDepartmentRequest;
use App\Http\Resources\Api\MasterData\DepartmentResource;
use App\Models\Department;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{

    public function __construct(
        private TenantService $tenantService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $guard = $request->get('guard', 'web');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $perPage = min(max($perPage, 1), 100);

        $query = Department::query();
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        $query->orderBy($sortBy, $sortOrder);
        $branches = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'success' => true,
            'message' => 'Departments retrieved successfully.',
            'data' => DepartmentResource::collection($branches->items()),
            'meta' => [
                'current_page' => $branches->currentPage(),
                'last_page' => $branches->lastPage(),
                'per_page' => $branches->perPage(),
                'total' => $branches->total(),
                'from' => $branches->firstItem(),
                'to' => $branches->lastItem(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request)
    {
        try {
            DB::beginTransaction();
            $department = Department::create([
                'company_id' => $this->tenantService->getCompanyId(),
                'name' => $request->input('name'),
            ]);
            DB::commit();
            Log::info('Department created', ['department_id' => $department->id, 'name' => $department->name]);
            return response()->json([
                'success' => true,
                'message' => 'Department created successfully.',
                'data' => new DepartmentResource($department)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create department.',
                'data' => null
            ], 500);
        }
    }


    public function search(Request $request)
    {
        try {
            $query = $request->get('query', '');
            $departments = Department::where('name', 'like', "%$query%")
                ->where('company_id', $this->tenantService->getCompanyId())
                ->limit(5)
                ->get();
            return response()->json([
                'success' => true,
                'message' => 'Departments retrieved successfully.',
                'data' => DepartmentResource::collection($departments)
            ]);
        } catch (\Exception $e) {
            Log::error('Department search failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve departments.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        try {
            DB::beginTransaction();
            $department->update([
                'name' => $request->input('name'),
            ]);
            DB::commit();
            Log::info('Department updated', ['department_id' => $department->id, 'name' => $department->name]);
            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully.',
                'data' => new DepartmentResource($department)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update department.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        try {
            DB::beginTransaction();
            $department->delete();
            DB::commit();
            Log::info('Department deleted', ['department_id' => $department->id, 'name' => $department->name]);
            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department.',
                'data' => null
            ], 500);
        }
    }
}
